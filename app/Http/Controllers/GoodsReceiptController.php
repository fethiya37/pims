<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\Supplier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class GoodsReceiptController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $locations = Location::where('type', 'store')->orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $locations = Location::where('id', $user->location_id)
                ->where('type', 'store')
                ->orderBy('id', 'desc')
                ->get();
            $isSuperAdmin = false;
        }

        $products = Product::orderBy('name', 'asc')->get();
        $suppliers = Supplier::all();

        return view('pages.goods_receipts.index', compact('locations', 'products', 'suppliers', 'isSuperAdmin'));
    }

    public function getData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        $req = new DataTableRequest($request, [
            0 => 'id',
            1 => 'reference_number',
            2 => 'receipt_date',
            3 => 'location_id',
            4 => 'supplier_id',
            5 => 'id',
            6 => 'status',
        ], 'created_at');

        $base = GoodsReceipt::query()
            ->with(['location', 'supplier', 'user', 'items.product'])
            ->when(!$isSuperAdmin, fn($q) => $q->where('location_id', $user->location_id));

        $dtq = (new DataTableQuery($base, $req))
    ->searchable(['reference_number', 'delivered_by', 'notes', 'status'])
    ->searchDates(['receipt_date'])
    ->searchRelations([
        'location' => ['name'],
        'supplier' => ['name'],
        'items.product' => ['name'],
    ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($receipt, $no) {
            $badge = $receipt->status === 'received' ? 'success'
                   : ($receipt->status === 'cancelled' ? 'danger' : 'warning');

            if ($receipt->items->count() > 0) {
                $parts = [];
                foreach ($receipt->items as $item) {
                    $name = e(optional($item->product)->name ?? 'N/A');
                    $qty  = e($item->quantity);
                    $parts[] = '<div class="d-flex justify-content-between"><span>' . $name . '</span><span class="text-muted ml-2">(' . $qty . ')</span></div>';
                }
                $itemsSummary = '<div style="font-size: 0.85rem; line-height: 1.3; min-width: 180px;">' . implode('', $parts) . '</div>';
            } else {
                $itemsSummary = '<span class="text-muted">No items</span>';
            }

            $actions = '<button type="button" class="btn btn-success btn-sm view-receipt-btn" data-id="' . $receipt->id . '"><i class="fas fa-eye"></i> View</button>';

            if ($receipt->status === 'draft') {
                $actions .= ' <button type="button" class="btn btn-info btn-sm edit-receipt-btn" data-id="' . $receipt->id . '"><i class="fas fa-edit"></i></button>';
                $actions .= ' <a href="' . route('goods-receipts.receive', $receipt->id) . '" class="btn btn-success btn-sm" onclick="return confirm(\'Mark this receipt as received?\');">Receive</a>';
                $actions .= ' <form action="' . route('goods-receipts.destroy', $receipt->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this receipt?\');">'
                          . csrf_field() . method_field('DELETE')
                          . '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
            }

            return [
                'no'        => $no,
                'reference' => e($receipt->reference_number ?? '#' . $receipt->id),
                'date'      => $receipt->receipt_date ? \Carbon\Carbon::parse($receipt->receipt_date)->toFormattedDateString() : 'N/A',
                'location'  => e(optional($receipt->location)->name ?? 'N/A'),
                'supplier'  => e(optional($receipt->supplier)->name ?? 'N/A'),
                'items'     => $itemsSummary,
                'status'    => '<span class="badge badge-' . $badge . '">' . e(ucfirst($receipt->status)) . '</span>',
                'actions'   => $actions,
            ];
        });
    }

    public function view($id): View
    {
        $receipt = GoodsReceipt::with(['location', 'supplier', 'user', 'items.product'])->findOrFail($id);

        return view('pages.goods_receipts.partials.view', compact('receipt'));
    }

    public function editForm($id): View
    {
        $receipt = GoodsReceipt::with('items.product')->findOrFail($id);

        if ($receipt->status !== 'draft') {
            abort(403, 'Only draft receipts can be edited.');
        }

        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $locations = Location::where('type', 'store')->orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $locations = Location::where('id', $user->location_id)->where('type', 'store')->orderBy('id', 'desc')->get();
            $isSuperAdmin = false;
        }

        $products = Product::orderBy('name', 'asc')->get();
        $suppliers = Supplier::all();

        return view('pages.goods_receipts.partials.edit-form', compact(
            'receipt', 'locations', 'products', 'suppliers', 'isSuperAdmin'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'receipt_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'delivered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'required|date',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'store') {
            return back()->with('error', 'Goods Receipt can only be created for Store locations.');
        }

        DB::beginTransaction();

        try {
            $receipt = GoodsReceipt::create([
                'location_id' => $request->location_id,
                'supplier_id' => $request->supplier_id,
                'receipt_date' => $request->receipt_date ?? now(),
                'reference_number' => $request->reference_number,
                'delivered_by' => $request->delivered_by,
                'status' => 'draft',
                'notes' => $request->notes,
                'user_id' => auth()->id(),
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $totalUnits,
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('goods-receipts.index')
                ->with('success', 'Goods Receipt created successfully. Click "Receive" to add to inventory.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating receipt: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $receipt = GoodsReceipt::with('items')->findOrFail($id);

        if ($receipt->status !== 'draft') {
            return back()->with('error', 'Only draft receipts can be edited.');
        }

        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'supplier_id' => 'nullable|exists:suppliers,id',
            'receipt_date' => 'nullable|date',
            'reference_number' => 'nullable|string|max:255',
            'delivered_by' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.lot_number' => 'nullable|string|max:255',
            'items.*.expiry_date' => 'required|date',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'store') {
            return back()->with('error', 'Goods Receipt can only be created for Store locations.');
        }

        DB::beginTransaction();

        try {
            $receipt->update([
                'location_id' => $request->location_id,
                'supplier_id' => $request->supplier_id,
                'receipt_date' => $request->receipt_date ?? now(),
                'reference_number' => $request->reference_number,
                'delivered_by' => $request->delivered_by,
                'notes' => $request->notes,
            ]);

            $receipt->items()->delete();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                GoodsReceiptItem::create([
                    'goods_receipt_id' => $receipt->id,
                    'product_id' => $item['product_id'],
                    'lot_number' => $item['lot_number'] ?? null,
                    'expiry_date' => $item['expiry_date'] ?? null,
                    'quantity' => $totalUnits,
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
                ]);
            }

            DB::commit();

            return redirect()->route('goods-receipts.index')
                ->with('success', 'Goods Receipt updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating receipt: ' . $e->getMessage());
        }
    }

    public function receive($id)
    {
        DB::beginTransaction();

        try {
            $receipt = GoodsReceipt::with('items.product')->findOrFail($id);

            if ($receipt->status !== 'draft') {
                return back()->with('error', 'This receipt has already been processed.');
            }

            foreach ($receipt->items as $item) {
                $stockBatch = StockBatch::where('product_id', $item->product_id)
                    ->where('location_id', $receipt->location_id)
                    ->where('lot_number', $item->lot_number ?? null)
                    ->where('expiry_date', $item->expiry_date ?? null)
                    ->first();

                if ($stockBatch) {
                    $stockBatch->increment('quantity', $item->quantity);
                } else {
                    StockBatch::create([
                        'product_id' => $item->product_id,
                        'location_id' => $receipt->location_id,
                        'lot_number' => $item->lot_number ?? null,
                        'expiry_date' => $item->expiry_date ?? null,
                        'quantity' => $item->quantity,
                    ]);
                }

                InventoryTransaction::create([
                    'product_id' => $item->product_id,
                    'from_location_id' => null,
                    'to_location_id' => $receipt->location_id,
                    'transaction_type' => 'receiving',
                    'reference' => 'GR-' . $receipt->id,
                    'lot_number' => $item->lot_number ?? null,
                    'expiry_date' => $item->expiry_date ?? null,
                    'quantity' => $item->quantity,
                    'user_id' => auth()->id(),
                    'notes' => 'Received from Supplier: ' . ($receipt->supplier->name ?? 'N/A'),
                ]);
            }

            $receipt->status = 'received';
            $receipt->save();

            DB::commit();

            return redirect()->route('goods-receipts.index')
                ->with('success', 'Goods Receipt #' . $receipt->id . ' marked as received. Stock updated.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error processing receipt: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        try {
            $receipt = GoodsReceipt::findOrFail($id);

            if ($receipt->status === 'received') {
                return back()->with('error', 'Cannot delete a received receipt.');
            }

            $receipt->items()->delete();
            $receipt->delete();

            return redirect()->route('goods-receipts.index')
                ->with('success', 'Goods Receipt deleted successfully.');
        } catch (\Exception $e) {
            return back()->with('error', 'Error deleting receipt: ' . $e->getMessage());
        }
    }
}