<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\InventoryTransaction;
use App\Models\Location;
use App\Models\Product;
use App\Models\ProductSale;
use App\Models\ProductSaleItem;
use App\Models\StockBatch;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProductSaleController extends Controller
{
    public function index(): View
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        if ($isSuperAdmin) {
            $locations = Location::where('type', 'sale')->orderBy('id', 'desc')->get();
        } else {
            $locations = Location::where('id', $user->location_id)->where('type', 'sale')->get();
        }

        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.sales.index', compact('locations', 'products', 'isSuperAdmin'));
    }

    public function getData(Request $request): JsonResponse
    {
        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        $req = new DataTableRequest($request, [
            0 => 'id',
            1 => 'id',
            2 => 'created_at',
            3 => 'location_id',
            4 => 'invoice_no',
            5 => 'id',
            6 => 'total_amount',
            7 => 'status',
        ], 'created_at');

        $base = ProductSale::query()
            ->with(['location', 'user', 'items.product'])
            ->when(!$isSuperAdmin, fn($q) => $q->where('location_id', $user->location_id));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['invoice_no', 'notes'])
            ->searchRelations([
                'location' => ['name'],
                'user'     => ['name'],
            ]);

        [$total, $filtered, $rows] = $dtq->paginate();

        $statusClasses = [
            'pending'   => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];

        return DataTableResponse::make($req->draw, $total, $filtered, $rows, function ($sale, $no) use ($statusClasses) {
            if ($sale->items->count() > 0) {
                $parts = [];
                foreach ($sale->items as $item) {
                    $name  = e(optional($item->product)->name ?? 'N/A');
                    $qty   = e($item->quantity);
                    $price = number_format($item->unit_price, 2);
                    $parts[] = '<div class="d-flex justify-content-between"><span>' . $name . '</span><span class="text-muted ml-2">' . $qty . ' × ' . $price . '</span></div>';
                }
                $itemsSummary = '<div style="font-size: 0.85rem; line-height: 1.3; min-width: 200px;">' . implode('', $parts) . '</div>';
            } else {
                $itemsSummary = '<span class="text-muted">No items</span>';
            }

            $statusClass = $statusClasses[$sale->status] ?? 'secondary';
            $statusBadge = '<span class="badge badge-' . $statusClass . '">' . e(ucfirst($sale->status)) . '</span>';

            $actions = '';

            if ($sale->status === 'pending') {
                $actions .= '<button type="button" class="btn btn-info btn-sm edit-sale-btn" data-id="' . $sale->id . '"><i class="fas fa-edit"></i></button>';
                $actions .= ' <a href="' . route('sales.complete', $sale->id) . '" class="btn btn-success btn-sm" onclick="return confirm(\'Complete this sale? Stock will be deducted.\');"><i class="fas fa-check"></i> Complete</a>';
                $actions .= ' <form action="' . route('sales.destroy', $sale->id) . '" method="POST" class="d-inline" onsubmit="return confirm(\'Delete this sale?\');">'
                          . csrf_field() . method_field('DELETE')
                          . '<button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash"></i></button></form>';
            }

            $actions .= ' <button type="button" class="btn btn-secondary btn-sm view-sale-btn" data-id="' . $sale->id . '"><i class="fas fa-eye"></i></button>';

            return [
                'no'         => $no,
                'reference'  => '#' . $sale->id,
                'date'       => $sale->created_at ? $sale->created_at->toFormattedDateString() : 'N/A',
                'location'   => e(optional($sale->location)->name ?? 'N/A'),
                'invoice'    => e($sale->invoice_no ?? 'N/A'),
                'items'      => $itemsSummary,
                'total'      => number_format($sale->total_amount, 2),
                'status'     => $statusBadge,
                'actions'    => $actions,
            ];
        });
    }

    public function view($id): View
    {
        $sale = ProductSale::with(['location', 'user', 'items.product'])->findOrFail($id);

        $statusClasses = [
            'pending'   => 'warning',
            'completed' => 'success',
            'cancelled' => 'danger',
        ];
        $statusClass = $statusClasses[$sale->status] ?? 'secondary';

        return view('pages.sales.partials.view', compact('sale', 'statusClass'));
    }

    public function editForm($id): View
    {
        $sale = ProductSale::with('items.product')->findOrFail($id);

        if ($sale->status !== 'pending') {
            abort(403, 'Only pending sales can be edited.');
        }

        $user = Auth::user();
        $isSuperAdmin = $user->role && $user->role->role_name === 'Super Admin';

        if ($isSuperAdmin) {
            $locations = Location::where('type', 'sale')->orderBy('id', 'desc')->get();
        } else {
            $locations = Location::where('id', $user->location_id)->where('type', 'sale')->get();
        }

        $products = Product::orderBy('name', 'asc')->get();

        return view('pages.sales.partials.edit-form', compact(
            'sale', 'locations', 'products', 'isSuperAdmin'
        ));
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
        if (!$location || $location->type !== 'sale') {
            return back()->with('error', 'Sales can only be recorded at Sale locations.');
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
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
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
        if (!$location || $location->type !== 'sale') {
            return back()->with('error', 'Sales can only be recorded at Sale locations.');
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
                    'package' => $item['full_packages'],
                    'unit' => $item['extra_units'] ?? 0,
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
                        'notes' => 'Sale at ' . $sale->location->name,
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

            $sale->update(['status' => 'completed']);

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