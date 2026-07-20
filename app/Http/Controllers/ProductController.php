<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\Category;
use App\Models\OpeningQuantity;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use App\Models\ProductLocationSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(): View
    {
        $products = Product::orderBy('id', 'desc')->with(['category', 'openingQuantities.location'])->get();
        $categories = Category::all();
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $locations = Location::orderBy('id', 'desc')->get();
        } else {
            $locations = $user->location
                ? Location::where('type', $user->location->type)->orderBy('id', 'desc')->get()
                : collect();
        }

        return view('pages.products.product', compact('products', 'categories', 'locations'));
    }

    public function openingQuantities($productId): View
    {
        $product = Product::with(['openingQuantities.location'])->findOrFail($productId);
        $locations = Location::orderBy('id', 'desc')->get();
        return view('pages.products.opening_quantities', compact('product', 'locations'));
    }

    public function reorderSettings($productId): View
    {
        $product = Product::findOrFail($productId);
        $locations = Location::orderBy('name')->get();
        $settings = ProductLocationSetting::where('product_id', $productId)
            ->get()
            ->keyBy('location_id');

        return view('pages.products.reorder_settings', compact('product', 'locations', 'settings'));
    }

    public function storeReorderSettings(Request $request, $productId)
    {
        $request->validate([
            'reorder_levels' => 'required|array',
            'reorder_levels.*.location_id' => 'required|exists:locations,id',
            'reorder_levels.*.reorder_quantity' => 'required|numeric|min:0',
        ]);

        $product = Product::findOrFail($productId);

        foreach ($request->reorder_levels as $level) {
            $reorderQuantity = $level['reorder_quantity'];

            // If product is pack type, convert packs to units
            if ($product->packaging_type === 'pack' && $product->default_pack_size > 0) {
                $reorderQuantity = $reorderQuantity * $product->default_pack_size;
            }

            ProductLocationSetting::updateOrCreate(
                [
                    'product_id' => $productId,
                    'location_id' => $level['location_id'],
                ],
                [
                    'reorder_quantity' => $reorderQuantity,
                ]
            );
        }

        return redirect()->route('products.reorder-settings', $productId)
            ->with('success', 'Reorder levels updated successfully.');
    }

    public function addProduct(Request $request)
    {
        $request->validate([
            'item_code' => 'required|string|unique:products,item_code',
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:50',
            'default_pack_size' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'packaging_type' => 'nullable|in:unit,pack',
        ]);

        try {
            Product::create([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'default_pack_size' => $request->default_pack_size ?? 1,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'packaging_type' => $request->packaging_type ?? 'unit',
            ]);

            return back()->with('success', 'Product added successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Error adding product: ' . $e->getMessage());
        }
    }

    public function editProduct(Request $request, $id)
    {
        $request->validate([
            'item_code' => 'required|string|unique:products,item_code,' . $id,
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:50',
            'default_pack_size' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'packaging_type' => 'nullable|in:unit,pack',
        ]);

        try {
            $product = Product::findOrFail($id);

            $product->update([
                'item_code' => $request->item_code,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'default_pack_size' => $request->default_pack_size ?? 1,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'packaging_type' => $request->packaging_type ?? 'unit',
            ]);

            return back()->with('success', 'Product updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Error updating product: ' . $e->getMessage());
        }
    }

    public function deleteProduct($id)
    {
        try {
            $product = Product::findOrFail($id);
            $hasStock = StockBatch::where('product_id', $id)->exists();

            if ($hasStock) {
                return back()->withErrors('Cannot delete this product because it has existing inventory records. Please adjust or remove the stock first.');
            }

            $product->delete();
            return back()->with('success', 'Product deleted successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Error deleting product: ' . $e->getMessage());
        }
    }

    public function storeOpeningQuantities(Request $request, $productId)
    {
        $product = Product::findOrFail($productId);

        $rules = [
            'entries' => 'required|array',
            'entries.*.location_id' => 'required|exists:locations,id',
            'entries.*.lot_number' => 'nullable|string|max:255',
            'entries.*.expiry_date' => 'required|date',
        ];

        if ($product->packaging_type === 'unit') {
            $rules['entries.*.quantity'] = 'required|numeric|min:0';
        } else {
            $rules['entries.*.packages'] = 'required|numeric|min:0';
            $rules['entries.*.extra_units'] = 'nullable|numeric|min:0';
        }

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }

        DB::beginTransaction();

        try {
            foreach ($request->entries as $entry) {
                $lotNumber = $entry['lot_number'] ?? null;
                $expiryDate = $entry['expiry_date'] ?? null;

                if ($product->packaging_type === 'unit') {
                    $quantity = $entry['quantity'];
                    $packages = null;
                    $extraUnits = 0;
                } else {
                    $packages = $entry['packages'];
                    $extraUnits = $entry['extra_units'] ?? 0;
                    $quantity = ($packages * $product->default_pack_size) + $extraUnits;
                }

                OpeningQuantity::create([
                    'product_id' => $productId,
                    'location_id' => $entry['location_id'],
                    'lot_number' => $lotNumber,
                    'expiry_date' => $expiryDate,
                    'quantity' => $quantity,
                    'package' => $packages,
                    'unit' => $product->unit,
                ]);

                $stockBatch = StockBatch::where('product_id', $productId)
                    ->where('location_id', $entry['location_id'])
                    ->where('lot_number', $lotNumber)
                    ->where('expiry_date', $expiryDate)
                    ->first();

                if ($stockBatch) {
                    $stockBatch->increment('quantity', $quantity);
                } else {
                    StockBatch::create([
                        'product_id' => $productId,
                        'location_id' => $entry['location_id'],
                        'lot_number' => $lotNumber,
                        'expiry_date' => $expiryDate,
                        'quantity' => $quantity,
                        'package' => $packages,
                        'unit' => $product->unit,
                    ]);
                }

                InventoryTransaction::create([
                    'product_id' => $productId,
                    'from_location_id' => null,
                    'to_location_id' => $entry['location_id'],
                    'transaction_type' => 'opening',
                    'reference' => 'Opening Stock Entry',
                    'lot_number' => $lotNumber,
                    'expiry_date' => $expiryDate,
                    'quantity' => $quantity,
                    'user_id' => auth()->id(),
                    'notes' => 'Initial opening stock',
                    'package' => $packages,
                    'unit' => $product->unit,
                ]);
            }

            DB::commit();

            return redirect()->route('products.opening-quantities', $productId)
                ->with('success', 'Opening quantities added successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error adding opening quantities: ' . $e->getMessage());
        }
    }

    public function destroyOpeningQuantity($id)
    {
        $openingQuantity = OpeningQuantity::findOrFail($id);

        DB::beginTransaction();

        try {
            $stockBatch = StockBatch::where('product_id', $openingQuantity->product_id)
                ->where('location_id', $openingQuantity->location_id)
                ->where('lot_number', $openingQuantity->lot_number)
                ->where('expiry_date', $openingQuantity->expiry_date)
                ->first();

            if ($stockBatch) {
                $newQuantity = $stockBatch->quantity - $openingQuantity->quantity;
                if ($newQuantity < 0) {
                    $newQuantity = 0;
                }
                $stockBatch->update(['quantity' => $newQuantity]);

                InventoryTransaction::create([
                    'product_id' => $openingQuantity->product_id,
                    'from_location_id' => $openingQuantity->location_id,
                    'to_location_id' => null,
                    'transaction_type' => 'adjustment',
                    'reference' => 'Opening Quantity Deletion #' . $openingQuantity->id,
                    'lot_number' => $openingQuantity->lot_number,
                    'expiry_date' => $openingQuantity->expiry_date,
                    'quantity' => $openingQuantity->quantity,
                    'user_id' => auth()->id(),
                    'notes' => 'Reverse opening stock entry',
                    'package' => $openingQuantity->package,
                    'unit' => $openingQuantity->unit,
                ]);
            }

            $openingQuantity->delete();

            DB::commit();

            return back()->with('success', 'Opening quantity deleted successfully. Stock adjusted.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors('Error deleting opening quantity: ' . $e->getMessage());
        }
    }
}