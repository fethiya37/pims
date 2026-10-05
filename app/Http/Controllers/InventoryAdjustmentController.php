<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\InventoryAdjustment;
use App\Models\InventoryAdjustmentItem;
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
            1 => 'id',
            2 => 'location_id',
            3 => 'id',
            4 => 'status',
            5 => 'requested_by',
            6 => 'approved_by',
            7 => 'created_at',
        ], 'created_at');

        $base = InventoryAdjustment::query()
            ->with(['location', 'requestedBy', 'approvedBy', 'items.product'])
            ->when(!$isSuperAdmin, fn($q) => $q->where('location_id', $user->location_id));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['reason'])
            ->searchRelations([
                'location'    => ['name'],
                'requestedBy' => ['name'],
                'approvedBy'  => ['name'],
            ]);

        [$total, $filtered, $rows] = $dtq->paginate();

        $statusClasses = [
            'pending'  => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
        ];

        return DataTableResponse::make($req->draw, $total, $filtered, $rows, function ($adjustment, $no) use ($statusClasses, $isSuperAdmin) {
            $itemsSummary = '<span class="text-muted">No items</span>';

            if ($adjustment->items->count() > 0) {
                $parts = [];
                foreach ($adjustment->items as $item) {
                    $name = e(optional($item->product)->name ?? 'N/A');
                    $qty  = e($item->quantity);
                    $dir  = $item->adjustment_type === 'IN'
                        ? '<span class="badge badge-success">IN</span>'
                        : '<span class="badge badge-danger">OUT</span>';
                    $parts[] = '<div class="d-flex justify-content-between"><span>' . $name . ' ' . $dir . '</span><span class="text-muted ml-2">(' . $qty . ')</span></div>';
                }
                $itemsSummary = '<div style="font-size: 0.85rem; line-height: 1.3; min-width: 220px;">' . implode('', $parts) . '</div>';
            }

            $statusClass = $statusClasses[$adjustment->status] ?? 'secondary';
            $statusBadge = '<span class="badge badge-' . $statusClass . '">' . e(ucfirst($adjustment->status)) . '</span>';

            $actions = '';

            if ($adjustment->status === 'pending') {
                $actions .= '<button type="button" class="btn btn-info btn-sm edit-adjustment-btn" data-id="' . $adjustment->id . '"><i class="fas fa-edit"></i></button>';
                $actions .= ' <form action="' . route('inventory-adjustments.destroy', $adjustment->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this request?\');">'
                          . csrf_field() . method_field('DELETE')
                          . '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';

                if ($isSuperAdmin) {
                    $actions .= ' <a href="' . route('inventory-adjustments.approve', $adjustment->id) . '" class="btn btn-success btn-sm" onclick="return confirm(\'Approve this adjustment? Stock will be updated.\');"><i class="fas fa-check"></i> Approve</a>';
                    $actions .= ' <button type="button" class="btn btn-danger btn-sm reject-adjustment-btn" data-id="' . $adjustment->id . '"><i class="fas fa-times"></i></button>';
                }
            }

            $actions .= ' <button type="button" class="btn btn-secondary btn-sm view-adjustment-btn" data-id="' . $adjustment->id . '"><i class="fas fa-eye"></i></button>';

            return [
                'no'        => $no,
                'reference' => '#' . $adjustment->id,
                'location'  => e(optional($adjustment->location)->name ?? 'N/A'),
                'items'     => $itemsSummary,
                'status'    => $statusBadge,
                'requested' => e(optional($adjustment->requestedBy)->name ?? 'N/A'),
                'approved'  => e(optional($adjustment->approvedBy)->name ?? '—'),
                'date'      => $adjustment->created_at ? $adjustment->created_at->format('Y-m-d H:i') : 'N/A',
                'actions'   => $actions,
            ];
        });
    }

    public function view($id): View
    {
        $adjustment = InventoryAdjustment::with(['location', 'requestedBy', 'approvedBy', 'items.product'])->findOrFail($id);

        $statusClasses = ['pending' => 'warning', 'approved' => 'success', 'rejected' => 'danger'];
        $statusClass = $statusClasses[$adjustment->status] ?? 'secondary';

        return view('pages.inventory_adjustments.partials.view', compact('adjustment', 'statusClass'));
    }

    public function editForm($id): View
    {
        $adjustment = InventoryAdjustment::with('items.product')->findOrFail($id);

        if ($adjustment->status !== 'pending') {
            abort(403, 'Only pending adjustments can be edited.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        $locations = $isSuperAdmin
            ? Location::orderBy('id', 'desc')->get()
            : Location::where('id', $user->location_id)->get();

        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.inventory_adjustments.partials.edit-form', compact(
            'adjustment', 'locations', 'products', 'isSuperAdmin'
        ));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.adjustment_type' => 'required|in:IN,OUT',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'required|date',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $adjustment = InventoryAdjustment::create([
                'location_id' => $validated['location_id'],
                'status' => 'pending',
                'reason' => $validated['reason'] ?? null,
                'requested_by' => auth()->id(),
            ]);

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                if ($totalUnits <= 0) {
                    continue;
                }

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $totalUnits,
                    'adjustment_type' => $item['adjustment_type'],
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory-adjustments.index')
                ->with('success', 'Adjustment request submitted. Awaiting approval.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating adjustment: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $adjustment = InventoryAdjustment::with('items')->findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be edited.');
        }

        $validated = $request->validate([
            'location_id' => 'required|exists:locations,id',
            'reason' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.adjustment_type' => 'required|in:IN,OUT',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'required|date',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        DB::beginTransaction();

        try {
            $adjustment->update([
                'location_id' => $validated['location_id'],
                'reason' => $validated['reason'] ?? null,
            ]);

            $adjustment->items()->delete();

            foreach ($validated['items'] as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                if ($totalUnits <= 0) {
                    continue;
                }

                InventoryAdjustmentItem::create([
                    'inventory_adjustment_id' => $adjustment->id,
                    'product_id' => $item['product_id'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $totalUnits,
                    'adjustment_type' => $item['adjustment_type'],
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('inventory-adjustments.index')
                ->with('success', 'Adjustment request updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating adjustment: ' . $e->getMessage());
        }
    }

    public function approve($id)
    {
        $user = Auth::user();
        if (!$user->role || $user->role->role_name !== 'Super Admin') {
            return back()->with('error', 'Only Super Admin can approve adjustments.');
        }

        $adjustment = InventoryAdjustment::with('items.product')->findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be approved.');
        }

        DB::beginTransaction();

        try {
            foreach ($adjustment->items as $item) {
                $stockBatch = StockBatch::where('product_id', $item->product_id)
                    ->where('location_id', $adjustment->location_id)
                    ->where('lot_number', $item->lot_number ?? null)
                    ->where('expiry_date', $item->expiry_date ?? null)
                    ->first();

                if ($item->adjustment_type === 'IN') {
                    if ($stockBatch) {
                        $stockBatch->increment('quantity', $item->quantity);
                    } else {
                        StockBatch::create([
                            'product_id' => $item->product_id,
                            'location_id' => $adjustment->location_id,
                            'lot_number' => $item->lot_number ?? null,
                            'expiry_date' => $item->expiry_date ?? null,
                            'quantity' => $item->quantity,
                        ]);
                    }
                } else {
                    if (!$stockBatch || $stockBatch->quantity < $item->quantity) {
                        DB::rollBack();
                        $productName = optional($item->product)->name ?? 'N/A';
                        return back()->with('error', 'Not enough stock for ' . $productName . '. Cannot approve.');
                    }

                    $stockBatch->decrement('quantity', $item->quantity);
                }

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'from_location_id' => $item->adjustment_type === 'OUT' ? $adjustment->location_id : null,
                    'to_location_id' => $item->adjustment_type === 'IN' ? $adjustment->location_id : null,
                    'transaction_type' => 'adjustment',
                    'reference' => 'ADJ-' . $adjustment->id,
                    'lot_number' => $item->lot_number ?? null,
                    'expiry_date' => $item->expiry_date ?? null,
                    'quantity' => $item->quantity,
                    'user_id' => auth()->id(),
                    'notes' => 'Approved adjustment: ' . ($adjustment->reason ?? 'No reason'),
                ]);
            }

            $adjustment->update([
                'status' => 'approved',
                'approved_by' => auth()->id(),
                'approved_at' => now(),
            ]);

            DB::commit();

            return back()->with('success', 'Adjustment approved. Stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error approving adjustment: ' . $e->getMessage());
        }
    }

    public function reject(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user->role || $user->role->role_name !== 'Super Admin') {
            return back()->with('error', 'Only Super Admin can reject adjustments.');
        }

        $adjustment = InventoryAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be rejected.');
        }

        $adjustment->update([
            'status' => 'rejected',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
            'reason' => $request->remarks ?? $adjustment->reason,
        ]);

        return back()->with('success', 'Adjustment rejected.');
    }

    public function destroy($id)
    {
        $adjustment = InventoryAdjustment::findOrFail($id);

        if ($adjustment->status !== 'pending') {
            return back()->with('error', 'Only pending adjustments can be deleted.');
        }

        $adjustment->items()->delete();
        $adjustment->delete();

        return back()->with('success', 'Adjustment request deleted.');
    }
}