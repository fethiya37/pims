<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\TreatmentConsumption;
use App\Models\TreatmentConsumptionItem;
use App\Models\Location;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TreatmentConsumptionController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();

        if ($user->role && $user->role->role_name === 'Super Admin') {
            $consumptions = TreatmentConsumption::with(['patient', 'location', 'doctor', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('type', 'point_of_use')->orderBy('id', 'desc')->get();
            $isSuperAdmin = true;
        } else {
            $consumptions = TreatmentConsumption::where('location_id', $user->location_id)
                ->with(['patient', 'location', 'doctor', 'items.product'])
                ->orderBy('created_at', 'desc')
                ->get();
            $locations = Location::where('id', $user->location_id)->where('type', 'point_of_use')->get();
            $isSuperAdmin = false;
        }

        $patients = Patient::all();
        $products = Product::all();
        $users = \App\Models\User::whereHas('role', function ($query) {
            $query->where('role_name', 'like', '%doctor%');
        })->get();

        return view('pages.treatments.index', compact('consumptions', 'locations', 'patients', 'products', 'users', 'isSuperAdmin'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'location_id' => 'required|exists:locations,id',
            'treatment_date' => 'nullable|date',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'point_of_use') {
            return back()->with('error', 'Treatment consumption can only be recorded at Point of Use locations.');
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

            $consumption = TreatmentConsumption::create([
                'patient_id' => $request->patient_id,
                'location_id' => $request->location_id,
                'doctor_id' => auth()->id(),
                'treatment_date' => $request->treatment_date ?? now(),
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
                'status' => 'draft',
            ]);

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                TreatmentConsumptionItem::create([
                    'treatment_consumption_id' => $consumption->id,
                    'product_id' => $item['product_id'],
                    'packages' => $item['full_packages'],
                    'pack_size' => $packSize,
                    'quantity' => $totalUnits,
                ]);
            }

            DB::commit();

            return redirect()->route('treatments.index')
                ->with('success', 'Treatment consumption created successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error creating treatment: ' . $e->getMessage());
        }
    }

    public function update(Request $request, $id)
    {
        $consumption = TreatmentConsumption::with('items')->findOrFail($id);

        if ($consumption->status !== 'draft') {
            return back()->with('error', 'Only draft treatments can be edited.');
        }

        $request->validate([
            'patient_id' => 'nullable|exists:patients,id',
            'location_id' => 'required|exists:locations,id',
            'treatment_date' => 'nullable|date',
            'diagnosis' => 'nullable|string',
            'notes' => 'nullable|string',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.full_packages' => 'required|integer|min:0',
            'items.*.extra_units' => 'nullable|numeric|min:0',
        ]);

        $location = Location::find($request->location_id);
        if (!$location || $location->type !== 'point_of_use') {
            return back()->with('error', 'Treatment consumption can only be recorded at Point of Use locations.');
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

            $consumption->update([
                'patient_id' => $request->patient_id,
                'location_id' => $request->location_id,
                'treatment_date' => $request->treatment_date ?? now(),
                'diagnosis' => $request->diagnosis,
                'notes' => $request->notes,
            ]);

            $consumption->items()->delete();

            foreach ($request->items as $item) {
                $product = Product::findOrFail($item['product_id']);
                $packSize = $product->default_pack_size;
                $totalUnits = ($item['full_packages'] * $packSize) + ($item['extra_units'] ?? 0);

                TreatmentConsumptionItem::create([
                    'treatment_consumption_id' => $consumption->id,
                    'product_id' => $item['product_id'],
                    'packages' => $item['full_packages'],
                    'pack_size' => $packSize,
                    'quantity' => $totalUnits,
                ]);
            }

            DB::commit();

            return redirect()->route('treatments.index')
                ->with('success', 'Treatment updated successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error updating treatment: ' . $e->getMessage());
        }
    }

    public function complete($id)
    {
        $consumption = TreatmentConsumption::with('items.product')->findOrFail($id);

        if ($consumption->status !== 'draft') {
            return back()->with('error', 'Only draft treatments can be completed.');
        }

        DB::beginTransaction();

        try {
            $errors = [];

            foreach ($consumption->items as $item) {
                $qtyToConsume = $item->quantity;

                $batches = StockBatch::where('product_id', $item->product_id)
                    ->where('location_id', $consumption->location_id)
                    ->where('quantity', '>', 0)
                    ->orderBy('expiry_date', 'asc')
                    ->orderBy('created_at', 'asc')
                    ->get();

                foreach ($batches as $batch) {
                    if ($qtyToConsume <= 0) break;

                    $takeQty = min($qtyToConsume, $batch->quantity);

                    $batch->decrement('quantity', $takeQty);

                    InventoryTransaction::create([
                        'product_id' => $item->product_id,
                        'from_location_id' => $consumption->location_id,
                        'to_location_id' => null,
                        'transaction_type' => 'consumption',
                        'reference' => 'TC-' . $consumption->id,
                        'lot_number' => $batch->lot_number,
                        'expiry_date' => $batch->expiry_date,
                        'packages' => $item->packages,
                        'pack_size' => $item->pack_size,
                        'quantity' => $takeQty,
                        'cost_per_unit' => $batch->cost_per_unit,
                        'price_per_unit' => null,
                        'user_id' => auth()->id(),
                        'notes' => 'Treatment consumption for patient: ' . ($consumption->patient->full_name ?? 'N/A'),
                    ]);

                    $qtyToConsume -= $takeQty;
                }

                if ($qtyToConsume > 0) {
                    $errors[] = "Not enough stock for {$item->product->name}. Short by {$qtyToConsume} units.";
                }
            }

            if (!empty($errors)) {
                DB::rollBack();
                return back()->withErrors($errors);
            }

            $consumption->update([
                'status' => 'completed',
            ]);

            DB::commit();

            return back()->with('success', 'Treatment completed. Stock deducted successfully.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Error completing treatment: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $consumption = TreatmentConsumption::findOrFail($id);

        if ($consumption->status === 'completed') {
            return back()->with('error', 'Cannot delete a completed treatment.');
        }

        $consumption->items()->delete();
        $consumption->delete();

        return back()->with('success', 'Treatment deleted successfully.');
    }
}