<?php

namespace App\Http\Controllers;

use App\Models\ProductSale;
use App\Models\ProductSaleItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductSaleController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $sales = ProductSale::with(['location', 'user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('type', 'point_of_use')->orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $sales = ProductSale::where('location_id', $user->location_id)
                ->with(['location', 'user', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('id', $user->location_id)->where('type', 'point_of_use')->get();
            $isSuperAdmin = false;
        }

        $products = Product::all();

        return view('pages.sales.index', compact('sales', 'locations', 'products', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'sale_date' => 'nullable|date',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'nullable|in:cash,card,bank_transfer,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'point_of_use') {
            return back()->with('error', 'Sales can only be recorded at Point of Use locations.');
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $requestedQty = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                $availableQty = StockBatch::where('product_id', $item['product_id'])
                    ->where('location_id', $request->location_id)
                    ->sum('quantity');

                if ($availableQty < $requestedQty) {
                    $errors[] = "Not enough stock for {$product->name}. Available: {$availableQty}, Requested: {$requestedQty}.";
                }
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            $sale = ProductSale::create([
                'location_id' => $request->location_id,
                'sale_date' => $request->sale_date ?? now(),
                'invoice_no' => $request->invoice_no,
                'vat_rate' => $request->vat_rate ?? 0,
                'payment_type' => $request->payment_type,
                'notes' => $request->notes,
                'status' => 'pending',
                'user_id' => auth()->id(),
            ]);

            $subtotal = 0;
            $totalTax = 0;
            $vatRate = (float) ($request->vat_rate ?? 0);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);
                $lineTotal = $totalUnits * $item['unit_price'];
                $lineTax = $lineTotal * ($vatRate / 100);

                ProductSaleItem::create([
                    'product_sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $totalUnits,
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'total_tax' => $lineTax,
                ]);

                $subtotal += $lineTotal;
                $totalTax += $lineTax;
            }

            $sale->update([
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'total_amount' => $subtotal + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating sale: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $sale = ProductSale::with('items')->findOrFail($id);

        if ($sale->status !== 'pending') {
            return back()->with('error', 'Only pending sales can be edited.');
        }

        $request->validate([
            'location_id' => 'required|exists:locations,id',
            'sale_date' => 'nullable|date',
            'vat_rate' => 'nullable|numeric|min:0|max:100',
            'payment_type' => 'nullable|in:cash,card,bank_transfer,credit',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
            'items.*.unit_price' => 'required|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'point_of_use') {
            return back()->with('error', 'Sales can only be recorded at Point of Use locations.');
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $requestedQty = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                $availableQty = StockBatch::where('product_id', $item['product_id'])
                    ->where('location_id', $request->location_id)
                    ->sum('quantity');

                if ($availableQty < $requestedQty) {
                    $errors[] = "Not enough stock for {$product->name}. Available: {$availableQty}, Requested: {$requestedQty}.";
                }
            }

            if (!empty($errors)) {
                return back()->withErrors($errors)->withInput();
            }

            $sale->update([
                'location_id' => $request->location_id,
                'sale_date' => $request->sale_date ?? now(),
                'invoice_no' => $request->invoice_no,
                'vat_rate' => $request->vat_rate ?? 0,
                'payment_type' => $request->payment_type,
                'notes' => $request->notes,
            ]);

            $sale->items()->delete();

            $subtotal = 0;
            $totalTax = 0;
            $vatRate = (float) ($request->vat_rate ?? 0);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);
                $lineTotal = $totalUnits * $item['unit_price'];
                $lineTax = $lineTotal * ($vatRate / 100);

                ProductSaleItem::create([
                    'product_sale_id' => $sale->id,
                    'product_id' => $item['product_id'],
                    'quantity' => $totalUnits,
                    'unit_price' => $item['unit_price'],
                    'line_total' => $lineTotal,
                    'total_tax' => $lineTax,
                ]);

                $subtotal += $lineTotal;
                $totalTax += $lineTax;
            }

            $sale->update([
                'subtotal' => $subtotal,
                'total_tax' => $totalTax,
                'total_amount' => $subtotal + $totalTax,
            ]);

            DB::commit();

            return redirect()->route('sales.index')
                ->with('success', 'Sale updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating sale: ' . $e->getMessage());
        }
    }

    public function complete($id)
    {
        $sale = ProductSale::with('items.product')->findOrFail($id);

        if ($sale->status !== 'pending') {
            return back()->with('error', 'Only pending sales can be completed.');
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($sale->items as $item) {
                $qtyToSell = $item->quantity;

                $batches = StockBatch::where('product_id', $item->product_id)
                    ->where('location_id', $sale->location_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToSell <= 0) break;

                    $takeQty = min($qtyToSell, $batch->quantity);

                    $batch->decrement('quantity', $takeQty);

                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'from_location_id' => $sale->location_id,
                        'to_location_id' => null,
                        'transaction_type' => 'sale',
                        'reference' => 'SALE-' . $sale->id,
                        'lot_number' => $batch->lot_number,
                        'expiry_date' => $batch->expiry_date,
                        'quantity' => $takeQty,
                        'user_id' => auth()->id(),
                        'notes' => 'Walk-in sale',
                    ]);

                    $qtyToSell -= $takeQty;
                }

                if ($qtyToSell > 0) {
                    $errors[] = "Not enough stock for {$item->product->name}. Short by {$qtyToSell} units.";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors($errors);
            }

            $sale->update([
                'status' => 'completed',
            ]);

            DB::commit();

            return back()->with('success', 'Sale completed. Stock deducted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error completing sale: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $sale = ProductSale::findOrFail($id);

        if ($sale->status === 'completed') {
            return back()->with('error', 'Cannot delete a completed sale.');
        }

        $sale->items()->delete();
        $sale->delete();

        return back()->with('success', 'Sale deleted successfully.');
    }
}