<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Patient;
use App\Models\Product;
use App\Models\StockBatch;
use App\Models\TreatmentConsumption;
use App\Models\TreatmentConsumptionItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TreatmentConsumptionController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        if ($isSuperAdmin) {
            $locations = Location::where('type', 'point_of_use')->orderBy('id', 'desc')->get();
        } else {
            $locations = Location::where('id', $user->location_id)->where('type', 'point_of_use')->get();
        }

        $patients = Patient::all();
        $products = Product::orderBy('name', 'asc')->get();
        $users = User::whereHas('role', function ($query) {
            $query->where('role_name', 'like', '%doctor%');
        })->get();

        return view('pages.treatments.index', compact('locations', 'patients', 'products', 'users', 'isSuperAdmin'));
    }

    public function getData(Request $request): JsonResponse
{
    $user = Auth::user();
    $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

    $req = new DataTableRequest($request, [
        0 => 'id',
        1 => 'id',
        2 => 'created_at',
        3 => 'patient_id',
        4 => 'location_id',
        5 => 'id',
        6 => 'status',
    ], 'created_at');

    $base = TreatmentConsumption::query()
        ->with(['patient', 'location', 'doctor', 'items.product'])
        ->when(!$isSuperAdmin, fn($q) => $q->where('location_id', $user->location_id));

    $dtq = (new DataTableQuery($base, $req))
        ->searchable(['diagnosis', 'notes', 'status'])
        ->searchDates(['treatment_date', 'created_at'])
        ->searchRelations([
            'patient'  => ['full_name'],
            'location' => ['name'],
            'doctor'   => ['name'],
            'items.product' => ['name'],
        ]);

    [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();

    $statusClasses = [
        'draft'     => 'warning',
        'completed' => 'success',
        'cancelled' => 'danger',
    ];

    return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($consumption, $no) use ($statusClasses) {
        if ($consumption->items->count() > 0) {
            $parts = [];
            foreach ($consumption->items as $item) {
                $name = e(optional($item->product)->name ?? 'N/A');
                $qty  = e($item->quantity);
                $parts[] = '<div class="d-flex justify-content-between"><span>' . $name . '</span><span class="text-muted ml-2">(' . $qty . ')</span></div>';
            }
            $itemsSummary = '<div style="font-size: 0.85rem; line-height: 1.3; min-width: 180px;">' . implode('', $parts) . '</div>';
        } else {
            $itemsSummary = '<span class="text-muted">No items</span>';
        }

        $statusClass = $statusClasses[$consumption->status] ?? 'secondary';
        $statusBadge = '<span class="badge badge-' . $statusClass . '">' . e(ucfirst($consumption->status)) . '</span>';

        $actions = '';

        if ($consumption->status === 'draft') {
            $actions .= '<button type="button" class="btn btn-info btn-sm edit-treatment-btn" data-id="' . $consumption->id . '"><i class="fas fa-edit"></i></button>';
            $actions .= ' <a href="' . route('treatments.complete', $consumption->id) . '" class="btn btn-success btn-sm" onclick="return confirm(\'Complete this treatment? Stock will be deducted.\');"><i class="fas fa-check"></i> Complete</a>';
            $actions .= ' <form action="' . route('treatments.destroy', $consumption->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this treatment?\');">'
                      . csrf_field() . method_field('DELETE')
                      . '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
        }

        $actions .= ' <button type="button" class="btn btn-secondary btn-sm view-treatment-btn" data-id="' . $consumption->id . '"><i class="fas fa-eye"></i></button>';

        return [
            'no'        => $no,
            'reference' => '#' . $consumption->id,
            'date'      => $consumption->created_at ? $consumption->created_at->toFormattedDateString() : 'N/A',
            'patient'   => e(optional($consumption->patient)->full_name ?? 'N/A'),
            'location'  => e(optional($consumption->location)->name ?? 'N/A'),
            'items'     => $itemsSummary,
            'status'    => $statusBadge,
            'actions'   => $actions,
        ];
    });
}

    public function view($id): View
    {
        $consumption = TreatmentConsumption::with([
            'patient', 'location', 'doctor', 'items.product',
        ])->findOrFail($id);

        $statusClasses = [
            'draft'     => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        $statusClass = $statusClasses[$consumption->status] ?? 'secondary';

        return view('pages.treatments.partials.view', compact('consumption', 'statusClass'));
    }

    public function editForm($id): View
    {
        $consumption = TreatmentConsumption::with('items.product')->findOrFail($id);

        if ($consumption->status !== 'draft') {
            abort(403, 'Only draft treatments can be edited.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        if ($isSuperAdmin) {
            $locations = Location::where('type', 'point_of_use')->orderBy('id', 'desc')->get();
        } else {
            $locations = Location::where('id', $user->location_id)->where('type', 'point_of_use')->get();
        }

        $patients = Patient::all();
        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.treatments.partials.edit-form', compact(
            'consumption', 'locations', 'patients', 'products', 'isSuperAdmin'
        ));
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
                DB::rollBack();
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
                    'quantity' => $totalUnits,
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
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
                DB::rollBack();
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
                    'quantity' => $totalUnits,
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
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
                        'quantity' => $takeQty,
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

            $consumption->update(['status' => 'completed']);

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