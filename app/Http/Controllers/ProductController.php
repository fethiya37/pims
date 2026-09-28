<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\Category;
use App\Models\OpeningQuantity;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use App\Models\ProductLocationSetting;
use App\Models\ProductSequence;
use App\Services\ProductDeletionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;


class ProductController extends Controller
{
    private function generateItemCode()
    {
        return DB::transaction(function () {
            $sequence = ProductSequence::lockForUpdate()->first();
            $nextNumber = $sequence->last_number + 1;
            $sequence->update(['last_number' => $nextNumber]);
            return 'PRD-' . str_pad($nextNumber, 4, '0', STR_PAD_LEFT);
        });
    }

    public function index(): View
{
    $categories = Category::all();
    $user = Auth::user();

    if ($user->role && $user->role->role_name === 'Super Admin') {
        $locations = Location::orderBy('id', 'desc')->get();
    } else {
        $locations = $user->location
            ? Location::where('type', $user->location->type)->orderBy('id', 'desc')->get()
            : collect();
    }

    return view('pages.products.product', compact('categories', 'locations'));
}

public function getProductData(Request $request)
{
    $draw   = (int) $request->input('draw', 1);
    $start  = max(0, (int) $request->input('start', 0));
    $length = min(200, max(1, (int) $request->input('length', 25)));
    $search = trim((string) $request->input('search.value', ''));

    $columns = [
        0 => 'id',
        1 => 'item_code',
        2 => 'name',
        3 => 'category_id',
        4 => 'unit',
        5 => 'packaging_type',
        6 => 'status',
        7 => 'description',
    ];

    $colIndex = (int) $request->input('order.0.column', 0);
    $orderBy  = $columns[$colIndex] ?? 'id';
    $orderDir = $request->input('order.0.dir', 'desc') === 'asc' ? 'asc' : 'desc';

    $query = Product::with('category');

    if ($search !== '') {
        $query->where(function ($q) use ($search) {
            $q->where('item_code', 'like', "%{$search}%")
              ->orWhere('name', 'like', "%{$search}%")
              ->orWhere('unit', 'like', "%{$search}%")
              ->orWhere('description', 'like', "%{$search}%")
              ->orWhereHas('category', function ($c) use ($search) {
                  $c->where('name', 'like', "%{$search}%");
              });
        });
    }

    $totalRecords = Cache::remember('dt.products.total', 60, function () {
        return Product::count();
    });

    $recordsFiltered = $search === ''
        ? $totalRecords
        : (clone $query)->count();

    $products = $query
        ->orderBy($orderBy, $orderDir)
        ->skip($start)
        ->take($length)
        ->get();

    $data = [];
    $no = $start + 1;

    foreach ($products as $product) {
        $data[] = [
            'no'          => $no++,
            'item_code'   => '<span class="badge badge-info">' . e($product->item_code) . '</span>',
            'name'        => e($product->name),
            'category'    => e(optional($product->category)->name ?? '—'),
            'unit'        => e($product->unit ?? '—'),
            'packaging'   => $product->packaging_type === 'pack'
                ? '<span class="badge badge-warning">Pack</span><small class="d-block">' . e($product->default_pack_size ?? 1) . ' × ' . e($product->unit) . '</small>'
                : '<span class="badge badge-secondary">Unit</span>',
            'status'      => $product->status === 'active'
                ? '<span class="badge badge-success">Active</span>'
                : '<span class="badge badge-secondary">Inactive</span>',
            'description' => e($product->description ?? '—'),
            'actions'     => view('pages.products.partials.actions', compact('product'))->render(),
        ];
    }

    return response()->json([
        'draw'            => $draw,
        'recordsTotal'    => $totalRecords,
        'recordsFiltered' => $recordsFiltered,
        'data'            => $data,
    ]);
}

public function editProductForm($id)
{
    $product = Product::findOrFail($id);
    $categories = Category::all();

    return view('pages.products.partials.edit-form', compact('product', 'categories'));
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
            'name' => 'required|string|max:255',
            'category_id' => 'nullable|exists:categories,id',
            'unit' => 'nullable|string|max:50',
            'default_pack_size' => 'nullable|integer|min:1',
            'description' => 'nullable|string',
            'status' => 'nullable|in:active,inactive',
            'packaging_type' => 'nullable|in:unit,pack',
        ]);

        try {
            $itemCode = $this->generateItemCode();

            Product::create([
                'item_code' => $itemCode,
                'name' => $request->name,
                'category_id' => $request->category_id,
                'unit' => $request->unit,
                'default_pack_size' => $request->default_pack_size ?? 1,
                'description' => $request->description,
                'status' => $request->status ?? 'active',
                'packaging_type' => $request->packaging_type ?? 'unit',
            ]);

            return back()->with('success', 'Product added successfully. Code: ' . $itemCode);
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

    public function deleteProduct($id, ProductDeletionService $service)
{
    try {
        $product = Product::findOrFail($id);

        $service->deleteProduct($product);

        return back()->with('success', 'Product and all related records deleted successfully.');
    } catch (\Throwable $e) {
        report($e);
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