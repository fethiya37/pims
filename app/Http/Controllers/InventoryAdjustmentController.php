<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\InventoryAdjustment;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class InventoryAdjustmentController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $adjustments = InventoryAdjustment::with(['product', 'location', 'user'])
                ->latest()
                ->get();
            // Show all locations, no type filtering
            $locations = Location::orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $adjustments = InventoryAdjustment::where('location_id', $user->location_id)
                ->with(['product', 'location', 'user'])
                ->latest()
                ->get();
            // Show only the user's location (no type restriction)
            $locations = Location::where('id', $user->location_id)->get();
            $isSuperAdmin = false;
        }

        $products = Product::all();
        return view('pages.inventory_adjustments.index', compact('adjustments', 'locations', 'products', 'isSuperAdmin'));
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
                return back()->with('error', 'No matching stock batch found for this product/location/lot/expiry.');
            }

            if ($stockBatch->quantity < $totalUnits) {
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

        return redirect()->route('inventory-adjustments.index')
            ->with('success', 'Adjustment completed successfully.');
    }
}