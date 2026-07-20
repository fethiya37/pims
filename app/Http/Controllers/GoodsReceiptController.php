<?php

namespace App\Http\Controllers;

use App\Models\GoodsReceipt;
use App\Models\GoodsReceiptItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use App\Models\Supplier;
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
            $receipts = GoodsReceipt::with(['location', 'supplier', 'user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('type', 'store')->orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $receipts = GoodsReceipt::where('location_id', $user->location_id)
                ->with(['location', 'supplier', 'user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('id', $user->location_id)
                ->where('type', 'store')
                ->orderBy('id', 'desc')
                ->get();
            $isSuperAdmin = false;
        }

        $products = Product::all();
        $suppliers = Supplier::all();

        return view('pages.goods_receipts.index', compact('receipts', 'locations', 'products', 'suppliers', 'isSuperAdmin'));
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