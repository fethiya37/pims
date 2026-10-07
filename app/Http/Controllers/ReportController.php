<?php

namespace App\Http\Controllers;

use App\Http\DataTables\DataTableQuery;
use App\Http\DataTables\DataTableRequest;
use App\Http\DataTables\DataTableResponse;
use App\Models\InventoryTransaction;
use App\Models\Patient;
use App\Models\Product;
use App\Models\ProductLocationSetting;
use App\Models\ProductSale;
use App\Models\ProductSaleItem;
use App\Models\StockBatch;
use App\Models\TreatmentConsumption;
use App\Support\LocationScope;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function getAllowedLocationIds(): array
    {
        return LocationScope::allowedIds();
    }

    private function getLocationsForDropdown()
    {
        return LocationScope::forDropdown();
    }

    public function stockBalance(Request $request): View
    {
        return $this->renderStockBalance($request, 'overall');
    }

    public function stockBalanceOverall(Request $request): View
    {
        return $this->renderStockBalance($request, 'overall');
    }

    public function stockBalanceLocation(Request $request): View
    {
        return $this->renderStockBalance($request, 'location');
    }

    public function stockBalanceBatch(Request $request): View
    {
        return $this->renderStockBalance($request, 'batch');
    }

    private function renderStockBalance(Request $request, string $tab): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = LocationScope::resolveRequestedLocation($request->location_id, $allowedLocationIds);

        return view('pages.reports.stock_balance', compact(
            'products',
            'allowedLocations',
            'productId',
            'locationId'
        ))->with('activeTab', $tab);
    }

    public function getStockBalanceData(Request $request): JsonResponse
    {
        $tab = $request->input('tab', 'overall');
        $allowedLocationIds = $this->getAllowedLocationIds();

        $productId = $request->input('product_id');
        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);

        if ($tab === 'overall') {
            $req = new DataTableRequest($request, [
                0 => 'product_id',
                1 => 'total_quantity',
                2 => 'last_updated',
            ], 'last_updated');

            $base = StockBatch::query()
                ->select(
                    'product_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('MAX(updated_at) as last_updated')
                )
                ->whereIn('location_id', $allowedLocationIds)
                ->when($productId, fn($q) => $q->where('product_id', $productId))
                ->when($locationId, fn($q) => $q->where('location_id', $locationId))
                ->groupBy('product_id');

            $dtq = (new DataTableQuery($base, $req))
                ->searchRelations(['product' => ['name']]);

            [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
            $rows->load('product');
            $rows->each(fn($r) => $this->addPackBreakdown($r, $r->product));

            return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
                return [
                    'no'             => $no,
                    'product'        => e(optional($row->product)->name ?? 'N/A'),
                    'quantity_units' => e($row->quantity_units ?? '0'),
                    'quantity_pack'  => e($row->quantity_pack_display ?? '-'),
                    'last_updated'   => $row->last_updated ? Carbon::parse($row->last_updated)->format('Y-m-d H:i') : 'N/A',
                    'has_pack'       => $row->packaging_type === 'pack',
                ];
            });
        }

        if ($tab === 'location') {
            $req = new DataTableRequest($request, [
                0 => 'product_id',
                1 => 'location_id',
                2 => 'total_quantity',
                3 => 'last_updated',
            ], 'last_updated');

            $base = StockBatch::query()
                ->select(
                    'product_id',
                    'location_id',
                    DB::raw('SUM(quantity) as total_quantity'),
                    DB::raw('MAX(updated_at) as last_updated')
                )
                ->whereIn('location_id', $allowedLocationIds)
                ->when($productId, fn($q) => $q->where('product_id', $productId))
                ->when($locationId, fn($q) => $q->where('location_id', $locationId))
                ->groupBy('product_id', 'location_id');

            $dtq = (new DataTableQuery($base, $req))
                ->searchRelations([
                    'product'  => ['name'],
                    'location' => ['name'],
                ]);

            [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
            $rows->load(['product', 'location']);
            $rows->each(fn($r) => $this->addPackBreakdown($r, $r->product));

            return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
                return [
                    'no'             => $no,
                    'product'        => e(optional($row->product)->name ?? 'N/A'),
                    'location'       => e(optional($row->location)->name ?? 'N/A'),
                    'quantity_units' => e($row->quantity_units ?? '0'),
                    'quantity_pack'  => e($row->quantity_pack_display ?? '-'),
                    'last_updated'   => $row->last_updated ? Carbon::parse($row->last_updated)->format('Y-m-d H:i') : 'N/A',
                    'has_pack'       => $row->packaging_type === 'pack',
                ];
            });
        }

        $req = new DataTableRequest($request, [
            0 => 'product_id',
            1 => 'location_id',
            2 => 'lot_number',
            3 => 'expiry_date',
            4 => 'quantity',
            5 => 'updated_at',
        ], 'updated_at');

        $base = StockBatch::query()
            ->whereIn('location_id', $allowedLocationIds)
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['lot_number'])
            ->searchDates(['expiry_date'])
            ->searchRelations([
                'product'  => ['name'],
                'location' => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
        $rows->load(['product', 'location']);
        $rows->each(fn($r) => $this->addPackBreakdown($r, $r->product));

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
            return [
                'no'             => $no,
                'product'        => e(optional($row->product)->name ?? 'N/A'),
                'location'       => e(optional($row->location)->name ?? 'N/A'),
                'lot_number'     => e($row->lot_number ?? 'N/A'),
                'expiry'         => $row->expiry_date ? Carbon::parse($row->expiry_date)->format('Y-m-d') : 'N/A',
                'quantity_units' => e($row->quantity_units ?? '0'),
                'quantity_pack'  => e($row->quantity_pack_display ?? '-'),
                'last_updated'   => $row->updated_at ? Carbon::parse($row->updated_at)->format('Y-m-d H:i') : 'N/A',
                'has_pack'       => $row->packaging_type === 'pack',
            ];
        });
    }

    public function interLocationTransfer(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $products = Product::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromLocationId = $request->from_location_id;
        $toLocationId = $request->to_location_id;

        return view('pages.reports.inter_location_transfer', compact(
            'products',
            'fromDate',
            'toDate',
            'fromLocationId',
            'toLocationId',
            'allowedLocations'
        ));
    }

    public function getInterTransferData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $fromDate = $request->input('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $productId = $request->input('product_id');
        $fromLocationId = $request->input('from_location_id');
        $toLocationId = $request->input('to_location_id');

        $req = new DataTableRequest($request, [
            0 => 'created_at',
            1 => 'reference',
            2 => 'product_id',
            3 => 'lot_number',
            4 => 'from_location_id',
            5 => 'to_location_id',
            6 => 'quantity',
            7 => 'user_id',
        ], 'created_at');

        $base = InventoryTransaction::query()
            ->with(['product', 'fromLocation', 'toLocation', 'user'])
            ->where('transaction_type', 'transfer')
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->where(function ($q) use ($allowedLocationIds) {
                $q->whereIn('from_location_id', $allowedLocationIds)
                    ->orWhereIn('to_location_id', $allowedLocationIds);
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($fromLocationId, fn($q) => $q->where('from_location_id', $fromLocationId))
            ->when($toLocationId, fn($q) => $q->where('to_location_id', $toLocationId));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['reference', 'lot_number'])
            ->searchDates(['created_at'])
            ->searchRelations([
                'product'      => ['name'],
                'fromLocation' => ['name'],
                'toLocation'   => ['name'],
                'user'         => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
        $rows->each(fn($r) => $this->addPackBreakdown($r, $r->product));

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
            return [
                'no'             => $no,
                'date'           => $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A',
                'reference'      => e($row->reference ?? 'N/A'),
                'product'        => e(optional($row->product)->name ?? 'N/A'),
                'lot_number'     => e($row->lot_number ?? 'N/A'),
                'from'           => e(optional($row->fromLocation)->name ?? 'N/A'),
                'to'             => e(optional($row->toLocation)->name ?? 'N/A'),
                'quantity_units' => e($row->quantity_units ?? '0'),
                'quantity_pack'  => e($row->quantity_pack_display ?? '-'),
                'has_pack'       => $row->packaging_type === 'pack',
                'user'           => e(optional($row->user)->name ?? 'N/A'),
            ];
        });
    }

    public function treatmentConsumption(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();
        $patients = Patient::orderBy('full_name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $locationId = LocationScope::resolveRequestedLocation($request->location_id, $allowedLocationIds);
        $patientId = $request->patient_id;
        $productId = $request->product_id;

        $summary = TreatmentConsumption::whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->select(
                DB::raw('COUNT(id) as total_treatments'),
                DB::raw('SUM((
                    SELECT SUM(quantity) FROM treatment_consumption_items
                    WHERE treatment_consumption_items.treatment_consumption_id = treatment_consumptions.id
                )) as total_items_consumed')
            )
            ->first();

        return view('pages.reports.treatment_consumption', compact(
            'products',
            'patients',
            'fromDate',
            'toDate',
            'summary',
            'locationId',
            'allowedLocations'
        ));
    }

    public function getTreatmentData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $fromDate = $request->input('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);
        $patientId = $request->input('patient_id');
        $productId = $request->input('product_id');

        $req = new DataTableRequest($request, [
            0 => 'created_at',
            1 => 'id',
            2 => 'patient_id',
            3 => 'location_id',
            4 => 'doctor_id',
            5 => 'status',
        ], 'created_at');

        $base = TreatmentConsumption::query()
            ->with(['patient', 'location', 'doctor', 'items.product'])
            ->whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($productId, fn($q) => $q->whereHas('items', fn($s) => $s->where('product_id', $productId)));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['status', 'diagnosis', 'notes'])
            ->searchDates(['treatment_date', 'created_at'])
            ->searchRelations([
                'patient'  => ['full_name'],
                'location' => ['name'],
                'doctor'   => ['name'],
                'items.product' => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
            $itemsHtml = '<ul class="mb-0">';
            foreach ($row->items as $item) {
                $itemsHtml .= '<li>' . e(optional($item->product)->name ?? 'N/A') . ': ' . e($item->quantity) . '</li>';
            }
            $itemsHtml .= '</ul>';

            return [
                'no'        => $no,
                'date'      => $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A',
                'reference' => 'TC-#' . $row->id,
                'patient'   => e(optional($row->patient)->full_name ?? 'N/A'),
                'location'  => e(optional($row->location)->name ?? 'N/A'),
                'doctor'    => e(optional($row->doctor)->name ?? 'N/A'),
                'items'     => $itemsHtml,
                'status'    => e($row->status),
            ];
        });
    }

    public function salesReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $locationId = LocationScope::resolveRequestedLocation($request->location_id, $allowedLocationIds);
        $productId = $request->product_id;

        $summary = ProductSale::whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->select(
                DB::raw('COUNT(id) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_tax) as total_tax'),
                DB::raw('AVG(total_amount) as average_sale_value')
            )
            ->first();

        return view('pages.reports.sales_report', compact(
            'products',
            'fromDate',
            'toDate',
            'summary',
            'locationId',
            'allowedLocations'
        ));
    }

    public function getSalesReportData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $fromDate = $request->input('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);
        $productId = $request->input('product_id');

        $req = new DataTableRequest($request, [
            0 => 'created_at',
            1 => 'id',
            2 => 'invoice_no',
            3 => 'location_id',
            10 => 'user_id',
        ], 'created_at');

        $base = ProductSale::query()
            ->with(['location', 'user', 'items.product'])
            ->whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($productId, fn($q) => $q->whereHas('items', fn($s) => $s->where('product_id', $productId)));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['invoice_no', 'status'])
            ->searchDates(['created_at'])
            ->searchRelations([
                'location' => ['name'],
                'user'     => ['name'],
                'items.product' => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();

        $flat = collect();
        foreach ($rows as $sale) {
            foreach ($sale->items as $item) {
                $flat->push((object) [
                    'sale'  => $sale,
                    'item'  => $item,
                ]);
            }
        }

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $flat, function ($row, $no) {
            $sale = $row->sale;
            $item = $row->item;

            return [
                'no'         => $no,
                'date'       => $sale->created_at ? $sale->created_at->format('Y-m-d H:i') : 'N/A',
                'reference'  => 'SALE-#' . $sale->id,
                'invoice'    => e($sale->invoice_no ?? 'N/A'),
                'location'   => e(optional($sale->location)->name ?? 'N/A'),
                'product'    => e(optional($item->product)->name ?? 'N/A'),
                'qty'        => e($item->quantity),
                'unit_price' => number_format($item->unit_price, 2),
                'line_total' => number_format($item->line_total, 2),
                'tax'        => number_format($item->total_tax, 2),
                'total'      => number_format($item->line_total + $item->total_tax, 2),
                'user'       => e(optional($sale->user)->name ?? 'N/A'),
            ];
        });
    }

    public function getTopProductsData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $fromDate = $request->input('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);
        $productId = $request->input('product_id');

        $req = new DataTableRequest($request, [], 'total_revenue');

        $base = ProductSaleItem::query()
            ->select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity'),
                DB::raw('SUM(line_total) as total_revenue')
            )
            ->whereHas('sale', function ($q) use ($fromTs, $toTs, $locationId, $allowedLocationIds) {
                $q->whereIn('location_id', $allowedLocationIds)
                    ->whereBetween('created_at', [$fromTs, $toTs]);
                if ($locationId) {
                    $q->where('location_id', $locationId);
                }
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->groupBy('product_id');

        $dtq = (new DataTableQuery($base, $req))
            ->searchRelations(['product' => ['name']]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
        $rows->load('product');

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
            return [
                'no'             => $no,
                'product'        => e(optional($row->product)->name ?? 'N/A'),
                'total_quantity' => number_format($row->total_quantity ?? 0),
                'total_revenue'  => number_format($row->total_revenue ?? 0, 2),
            ];
        });
    }

    public function transactionReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $products = Product::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromLocationId = $request->from_location_id;
        $toLocationId = $request->to_location_id;
        $transactionType = $request->transaction_type;

        return view('pages.reports.transaction_report', compact(
            'products',
            'fromDate',
            'toDate',
            'fromLocationId',
            'toLocationId',
            'transactionType',
            'allowedLocations'
        ));
    }

    public function getTransactionData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $fromDate = $request->input('from_date', now()->subMonth()->format('Y-m-d'));
        $toDate = $request->input('to_date', now()->format('Y-m-d'));
        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $fromLocationId = $request->input('from_location_id');
        $toLocationId = $request->input('to_location_id');
        $productId = $request->input('product_id');
        $transactionType = $request->input('transaction_type');

        $req = new DataTableRequest($request, [
            0 => 'created_at',
            1 => 'reference',
            2 => 'transaction_type',
            3 => 'product_id',
            4 => 'lot_number',
            5 => 'expiry_date',
            6 => 'from_location_id',
            7 => 'to_location_id',
            8 => 'quantity',
            9 => 'user_id',
        ], 'created_at');

        $base = InventoryTransaction::query()
            ->with(['product', 'fromLocation', 'toLocation', 'user'])
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->where(function ($q) use ($allowedLocationIds) {
                $q->whereIn('from_location_id', $allowedLocationIds)
                    ->orWhereIn('to_location_id', $allowedLocationIds);
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($fromLocationId, fn($q) => $q->where('from_location_id', $fromLocationId))
            ->when($toLocationId, fn($q) => $q->where('to_location_id', $toLocationId))
            ->when($transactionType, fn($q) => $q->where('transaction_type', $transactionType));

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['reference', 'lot_number', 'transaction_type', 'notes'])
            ->searchDates(['expiry_date', 'created_at'])
            ->searchRelations([
                'product'      => ['name'],
                'fromLocation' => ['name'],
                'toLocation'   => ['name'],
                'user'         => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();
        $rows->each(fn($r) => $this->addPackBreakdown($r, $r->product));

        $badgeMap = [
            'transfer'    => 'primary',
            'sale'        => 'success',
            'consumption' => 'warning',
            'opening'     => 'info',
            'receiving'   => 'secondary',
        ];

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) use ($badgeMap) {
            $badge = $badgeMap[$row->transaction_type] ?? 'danger';

            return [
                'no'             => $no,
                'date'           => $row->created_at ? $row->created_at->format('Y-m-d H:i') : 'N/A',
                'reference'      => e($row->reference ?? 'N/A'),
                'type'           => '<span class="badge badge-' . $badge . '">' . e(ucfirst($row->transaction_type)) . '</span>',
                'product'        => e(optional($row->product)->name ?? 'N/A'),
                'lot_number'     => e($row->lot_number ?? 'N/A'),
                'expiry'         => $row->expiry_date ?? 'N/A',
                'from'           => e(optional($row->fromLocation)->name ?? 'N/A'),
                'to'             => e(optional($row->toLocation)->name ?? 'N/A'),
                'quantity_units' => e($row->quantity_units ?? '0'),
                'quantity_pack'  => e($row->quantity_pack_display ?? '-'),
                'has_pack'       => $row->packaging_type === 'pack',
                'user'           => e(optional($row->user)->name ?? 'N/A'),
            ];
        });
    }

    public function lowStockReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = LocationScope::resolveRequestedLocation($request->location_id, $allowedLocationIds);

        return view('pages.reports.low_stock', compact(
            'products',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function getLowStockData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $productId = $request->input('product_id');
        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);

        $settings = ProductLocationSetting::with(['product', 'location'])
            ->whereIn('location_id', $allowedLocationIds)
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->get();

        $rows = collect();
        foreach ($settings as $setting) {
            $currentStock = StockBatch::where('product_id', $setting->product_id)
                ->where('location_id', $setting->location_id)
                ->sum('quantity');

            if ($currentStock > $setting->reorder_quantity) {
                continue;
            }

            $product = $setting->product;
            if (!$product) {
                continue;
            }

            $packSize = $product->default_pack_size ?? 1;
            $packagingType = $product->packaging_type ?? 'unit';
            $unit = $product->unit ?? 'unit';

            if ($packagingType === 'pack') {
                $fullPacks = floor($currentStock / $packSize);
                $extraUnits = $currentStock % $packSize;
                $packDisplay = $fullPacks . ' pack' . ($fullPacks != 1 ? 's' : '') .
                    ($extraUnits > 0 ? ' + ' . $extraUnits . ' ' . $unit : '');
                $reorderDisplay = floor($setting->reorder_quantity / $packSize) . ' pack' .
                    (floor($setting->reorder_quantity / $packSize) != 1 ? 's' : '');
            } else {
                $packDisplay = null;
                $reorderDisplay = $setting->reorder_quantity . ' ' . $unit;
            }

            $rows->push((object) [
                'product'                    => $product,
                'location'                   => $setting->location,
                'current_stock_units'        => $currentStock . ' ' . $unit,
                'current_stock_pack_display' => $packDisplay,
                'reorder_display'            => $reorderDisplay,
                'packaging_type'             => $packagingType,
                'status'                     => $currentStock == 0 ? 'Out of Stock' : 'Low Stock',
            ]);
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $rows = $rows->filter(fn($r) =>
                stripos($r->product->name, $search) !== false ||
                stripos(optional($r->location)->name, $search) !== false
            )->values();
        }

        $start = max(0, (int) $request->input('start', 0));
        $length = min(200, max(1, (int) $request->input('length', 25)));
        $page = $rows->slice($start, $length)->values();

        $data = [];
        $no = $start + 1;
        foreach ($page as $r) {
            $data[] = [
                'no'                  => $no++,
                'product'             => e($r->product->name),
                'location'            => e(optional($r->location)->name ?? 'N/A'),
                'current_stock_units' => e($r->current_stock_units),
                'current_stock_pack'  => e($r->current_stock_pack_display ?? '-'),
                'has_pack'            => $r->packaging_type === 'pack',
                'reorder_display'     => e($r->reorder_display),
                'status'              => '<span class="badge badge-' . ($r->status === 'Out of Stock' ? 'danger' : 'warning') . '">' . e($r->status) . '</span>',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
        ]);
    }

    public function expiryReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = LocationScope::resolveRequestedLocation($request->location_id, $allowedLocationIds);

        $summary = [
            'expired' => 0,
            'urgent'  => 0,
            'soon'    => 0,
            'ok'      => 0,
        ];

        $baseQuery = StockBatch::whereIn('location_id', $allowedLocationIds)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->whereRaw('DATEDIFF(expiry_date, CURDATE()) <= 90');

        $summary['expired'] = (clone $baseQuery)->whereRaw('DATEDIFF(expiry_date, CURDATE()) < 0')->count();
        $summary['urgent'] = (clone $baseQuery)->whereRaw('DATEDIFF(expiry_date, CURDATE()) BETWEEN 0 AND 30')->count();
        $summary['soon'] = (clone $baseQuery)->whereRaw('DATEDIFF(expiry_date, CURDATE()) BETWEEN 31 AND 60')->count();
        $summary['ok'] = (clone $baseQuery)->whereRaw('DATEDIFF(expiry_date, CURDATE()) BETWEEN 61 AND 90')->count();

        return view('pages.reports.expiry_report', compact(
            'products',
            'summary',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function getExpiryData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $productId = $request->input('product_id');
        $locationId = LocationScope::resolveRequestedLocation($request->input('location_id'), $allowedLocationIds);

        $req = new DataTableRequest($request, [
            0 => 'product_id',
            1 => 'location_id',
            2 => 'lot_number',
            3 => 'quantity',
            4 => 'expiry_date',
        ], 'expiry_date');

        $base = StockBatch::query()
            ->with(['product', 'location'])
            ->whereIn('location_id', $allowedLocationIds)
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->whereRaw('DATEDIFF(expiry_date, CURDATE()) <= 90');

        $dtq = (new DataTableQuery($base, $req))
            ->searchable(['lot_number'])
            ->searchDates(['expiry_date'])
            ->searchRelations([
                'product'  => ['name'],
                'location' => ['name'],
            ]);

        [$recordsTotal, $recordsFiltered, $rows] = $dtq->paginate();

        return DataTableResponse::make($req->draw, $recordsTotal, $recordsFiltered, $rows, function ($row, $no) {
            $daysRemaining = Carbon::today()->diffInDays($row->expiry_date, false);
            $statusLabel = $daysRemaining < 0 ? 'EXPIRED' : ($daysRemaining <= 30 ? 'URGENT' : ($daysRemaining <= 60 ? 'SOON' : 'OK'));
            $badgeClass = $daysRemaining < 0 ? 'danger' : ($daysRemaining <= 30 ? 'warning' : ($daysRemaining <= 60 ? 'info' : 'primary'));

            return [
                'no'             => $no,
                'product'        => e(optional($row->product)->name ?? 'N/A'),
                'location'       => e(optional($row->location)->name ?? 'N/A'),
                'lot_number'     => e($row->lot_number ?? 'N/A'),
                'quantity'       => e($row->quantity),
                'expiry_date'    => $row->expiry_date ? Carbon::parse($row->expiry_date)->format('Y-m-d') : 'N/A',
                'days_remaining' => $daysRemaining,
                'status'         => '<span class="badge badge-' . $badgeClass . '">' . $statusLabel . '</span>',
            ];
        });
    }

    public function zeroStockReport(Request $request): View
    {
        $totalProducts = Product::count();
        $totalZeroStock = 0;

        return view('pages.reports.zero_stock', compact('totalProducts', 'totalZeroStock'));
    }

    public function getZeroStockData(Request $request): JsonResponse
    {
        $allowedLocationIds = $this->getAllowedLocationIds();

        $products = Product::with('category')->orderBy('name')->get();
        $rows = collect();

        foreach ($products as $product) {
            $hasStock = StockBatch::where('product_id', $product->id)
                ->whereIn('location_id', $allowedLocationIds)
                ->exists();

            if ($hasStock) {
                continue;
            }

            $rows->push((object) [
                'product' => $product,
                'current_stock_units' => '0 ' . ($product->unit ?? 'unit'),
                'packaging_type' => $product->packaging_type ?? 'unit',
                'unit' => $product->unit ?? 'unit',
                'pack_size' => $product->default_pack_size ?? 1,
            ]);
        }

        $search = trim((string) $request->input('search.value', ''));
        if ($search !== '') {
            $rows = $rows->filter(fn($r) =>
                stripos($r->product->name, $search) !== false ||
                stripos($r->product->item_code, $search) !== false
            )->values();
        }

        $start = max(0, (int) $request->input('start', 0));
        $length = min(200, max(1, (int) $request->input('length', 25)));
        $page = $rows->slice($start, $length)->values();

        $data = [];
        $no = $start + 1;
        foreach ($page as $r) {
            $data[] = [
                'no'                  => $no++,
                'item_code'           => '<span class="badge badge-info">' . e($r->product->item_code ?? 'N/A') . '</span>',
                'name'                => e($r->product->name),
                'category'            => e(optional($r->product->category)->name ?? 'N/A'),
                'current_stock_units' => '<span class="badge badge-danger badge-lg">' . e($r->current_stock_units) . '</span>',
                'packaging'           => $r->packaging_type === 'pack'
                    ? '<span class="badge badge-warning">Pack</span><small class="d-block">Size: ' . e($r->pack_size) . ' ' . e($r->unit) . '</small>'
                    : '<span class="badge badge-secondary">Unit</span>',
                'status'              => '<span class="badge badge-danger"><i class="fas fa-times-circle"></i> No Stock Record</span>',
            ];
        }

        return response()->json([
            'draw'            => (int) $request->input('draw', 1),
            'recordsTotal'    => $rows->count(),
            'recordsFiltered' => $rows->count(),
            'data'            => $data,
        ]);
    }

    private function addPackBreakdown($item, $product = null)
    {
        if (!$product) {
            $product = $item->product ?? null;
        }

        if (!$product) {
            $item->quantity_units = $item->quantity ?? 0;
            $item->quantity_pack_display = null;
            $item->packaging_type = 'unit';
            $item->unit = 'unit';
            return $item;
        }

        $packSize = $product->default_pack_size ?? 1;
        $packagingType = $product->packaging_type ?? 'unit';
        $unit = $product->unit ?? 'unit';
        $quantity = $item->quantity ?? $item->total_quantity ?? 0;

        $item->quantity_units = $quantity . ' ' . $unit;

        if ($packagingType === 'pack' && $packSize > 0) {
            $fullPacks = floor($quantity / $packSize);
            $extraUnits = $quantity % $packSize;
            $item->quantity_pack_display = $fullPacks . ' pack' . ($fullPacks != 1 ? 's' : '') .
                ($extraUnits > 0 ? ' + ' . $extraUnits . ' ' . $unit : '');
        } else {
            $item->quantity_pack_display = null;
        }

        $item->packaging_type = $packagingType;
        $item->unit = $unit;
        return $item;
    }
}