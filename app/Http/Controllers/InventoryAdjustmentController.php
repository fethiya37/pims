<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\InventoryAdjustment;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $locations = Location::orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $locations = Location::where('id', $user->location_id)->get();
            $isSuperAdmin = false;
        }

        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.inventory_adjustments.index', compact('locations', 'products', 'isSuperAdmin'));
    }

    public function getData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        $req = new DataTableRequest($request, [
            0 => 'id',
            1 => 'location_id',
            2 => 'product_id',
            3 => 'adjustment_type',
            4 => 'lot_number',
            5 => 'quantity',
            6 => 'expiry_date',
            7 => 'reason',
            8 => 'user_id',
            9 => 'created_at',
        ], 'created_at');

        $base = InventoryAdjustment::query()
            ->with(['product', 'location', 'user'])
            ->when(!$isSuperAdmin, fn($q) => $q->where('location_id', $user->location_id));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['lot_number', 'reason'])
            ->searchRelations([
                'product'  => ['name'],
                'location' => ['name'],
                'user'     => ['name'],
            ]);

        [$total, $filtered, $rows] = $dtq->paginate();

        return DataTableResponse::make($req->draw, $total, $filtered, $rows, function ($adjustment, $no) {
            $typeBadge = $adjustment->adjustment_type === 'IN'
                ? '<span class="badge badge-success">Stock In</span>'
                : '<span class="badge badge-danger">Stock Out</span>';

            return [
                'no'       => $no,
                'location' => e(optional($adjustment->location)->name ?? 'N/A'),
                'product'  => e(optional($adjustment->product)->name ?? 'N/A'),
                'type'     => $typeBadge,
                'lot'      => e($adjustment->lot_number ?? 'N/A'),
                'quantity' => e($adjustment->quantity),
                'expiry'   => $adjustment->expiry_date ? \Carbon\Carbon::parse($adjustment->expiry_date)->format('Y-m-d') : 'N/A',
                'reason'   => e($adjustment->reason ?? '-'),
                'user'     => e(optional($adjustment->user)->name ?? '-'),
                'date'     => $adjustment->created_at ? $adjustment->created_at->format('d M Y H:i') : 'N/A',
            ];
        });
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'location_id' => 'required|exists:locations,id',
            'lot_number' => 'nullable|string|max:255',
            'expiry_date' => 'required|date',
            'full_packages' => 'required|integer|min:0',
            'extra_units' => 'nullable|numeric|min:0',
            'adjustment_type' => 'required|in:IN,OUT',
            'reason' => 'nullable|string',
        ]);

        DB::beginTransaction();

        try {
            $product = Product::findOrFail($validated['product_id']);
            $packSize = $product->default_pack_size;
            $totalUnits = ($validated['full_packages'] * $packSize) + ($validated['extra_units'] ?? 0);

            $stockBatch = StockBatch::where('product_id', $validated['product_id'])
                ->where('location_id', $validated['location_id'])
                ->where('lot_number', $validated['lot_number'] ?? null)
                ->where('expiry_date', $validated['expiry_date'] ?? null)
                ->first();

            if ($validated['adjustment_type'] === 'IN') {
                if ($stockBatch) {
                    $stockBatch->increment('quantity', $totalUnits);
                } else {
                    StockBatch::create([
                        'product_id' => $validated['product_id'],
                        'location_id' => $validated['location_id'],
                        'lot_number' => $validated['lot_number'] ?? null,
                        'expiry_date' => $validated['expiry_date'] ?? null,
                        'quantity' => $totalUnits,
                    ]);
                }
            } else {
                if (!$stockBatch) {
                    DB::rollBack();
                    return back()->with('error', 'No matching stock batch found for this product/location/lot/expiry.');
                }

                if ($stockBatch->quantity < $totalUnits) {
                    DB::rollBack();
                    return back()->with('error', 'Not enough stock to deduct. Available: ' . $stockBatch->quantity);
                }

                $stockBatch->decrement('quantity', $totalUnits);
            }

            $adjustment = InventoryAdjustment::create([
                'product_id' => $validated['product_id'],
                'location_id' => $validated['location_id'],
                'lot_number' => $validated['lot_number'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'quantity' => $totalUnits,
                'adjustment_type' => $validated['adjustment_type'],
                'reason' => $validated['reason'],
                'user_id' => auth()->id(),
            ]);

            InventoryTransaction::create([
                'product_id' => $validated['product_id'],
                'from_location_id' => $validated['adjustment_type'] === 'OUT' ? $validated['location_id'] : null,
                'to_location_id' => $validated['adjustment_type'] === 'IN' ? $validated['location_id'] : null,
                'transaction_type' => 'adjustment',
                'reference' => 'Inventory Adjustment #' . $adjustment->id,
                'lot_number' => $validated['lot_number'] ?? null,
                'expiry_date' => $validated['expiry_date'] ?? null,
                'quantity' => $totalUnits,
                'user_id' => auth()->id(),
                'notes' => $validated['adjustment_type'] . ': ' . ($validated['reason'] ?? 'No reason provided'),
            ]);

            DB::commit();

            return redirect()->route('inventory-adjustments.index')
                ->with('success', 'Adjustment completed successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing adjustment: ' . $e->getMessage());
        }
    }
}