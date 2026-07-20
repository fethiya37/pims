<?php

namespace App\Http\Controllers;

use App\Models\Location;
use App\Models\Product;
use App\Models\ProductLocationSetting;
use App\Models\StockBatch;
use App\Models\InventoryTransaction;
use App\Models\TreatmentConsumption;
use App\Models\ProductSale;
use App\Models\ProductSaleItem;
use App\Models\Patient;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    public function stockBalance(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;
        $activeTab = $request->active_tab ?? 'overall';

        $overallBalances = collect();
        $locationBalances = collect();
        $batchBalances = collect();

        if ($request->has('product_id')) {
            $query = StockBatch::with(['product', 'location']);

            if (!empty($productId)) {
                $query->where('product_id', $productId);
            }

            if (!empty($locationId)) {
                $query->where('location_id', $locationId);
            }

            $batchBalances = $query->orderBy('product_id')->orderBy('location_id')->get();

            // Overall balances with pack breakdown
            $overallBalances = StockBatch::select(
                'product_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
                ->when(!empty($productId), fn($q) => $q->where('product_id', $productId))
                ->when(!empty($locationId), fn($q) => $q->where('location_id', $locationId))
                ->groupBy('product_id')
                ->with('product')
                ->get()
                ->map(function ($item) {
                    return $this->addPackBreakdown($item, $item->product);
                });

            // Location balances with pack breakdown
            $locationBalances = StockBatch::select(
                'product_id',
                'location_id',
                DB::raw('SUM(quantity) as total_quantity')
            )
                ->when(!empty($productId), fn($q) => $q->where('product_id', $productId))
                ->when(!empty($locationId), fn($q) => $q->where('location_id', $locationId))
                ->groupBy('product_id', 'location_id')
                ->with(['product', 'location'])
                ->get()
                ->map(function ($item) {
                    return $this->addPackBreakdown($item, $item->product);
                });

            // Batch balances – each batch already has quantity
            $batchBalances = $batchBalances->map(function ($batch) {
                return $this->addPackBreakdown($batch, $batch->product);
            });
        }

        return view('pages.reports.stock_balance', compact(
            'products',
            'locations',
            'overallBalances',
            'locationBalances',
            'batchBalances',
            'activeTab',
            'productId',
            'locationId'
        ));
    }

    public function interLocationTransfer(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $transfers = InventoryTransaction::with(['product', 'fromLocation', 'toLocation', 'user'])
            ->where('transaction_type', 'transfer')
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('from_location_id'), fn($q) => $q->where('from_location_id', $request->from_location_id))
            ->when($request->filled('to_location_id'), fn($q) => $q->where('to_location_id', $request->to_location_id))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($txn) {
                return $this->addPackBreakdown($txn, $txn->product);
            });

        return view('pages.reports.inter_location_transfer', compact(
            'transfers',
            'products',
            'locations',
            'fromDate',
            'toDate'
        ));
    }

    public function treatmentConsumption(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $patients = Patient::orderBy('full_name')->get();
        $locations = Location::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $consumptions = TreatmentConsumption::with(['patient', 'location', 'doctor', 'items.product'])
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('patient_id'), fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->location_id))
            ->when($request->filled('product_id'), function ($q) use ($request) {
                $q->whereHas('items', fn($sub) => $sub->where('product_id', $request->product_id));
            })
            ->orderByDesc('created_at')
            ->get();

        $summary = TreatmentConsumption::whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('patient_id'), fn($q) => $q->where('patient_id', $request->patient_id))
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->location_id))
            ->select(
                DB::raw('COUNT(id) as total_treatments'),
                DB::raw('SUM((
                    SELECT SUM(quantity) FROM treatment_consumption_items
                    WHERE treatment_consumption_items.treatment_consumption_id = treatment_consumptions.id
                )) as total_items_consumed')
            )
            ->first();

        return view('pages.reports.treatment_consumption', compact(
            'consumptions',
            'products',
            'patients',
            'locations',
            'fromDate',
            'toDate',
            'summary'
        ));
    }

    public function salesReport(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $sales = ProductSale::with(['location', 'user', 'items.product'])
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->location_id))
            ->when($request->filled('product_id'), function ($q) use ($request) {
                $q->whereHas('items', fn($sub) => $sub->where('product_id', $request->product_id));
            })
            ->orderByDesc('created_at')
            ->get();

        $summary = ProductSale::whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('location_id'), fn($q) => $q->where('location_id', $request->location_id))
            ->select(
                DB::raw('COUNT(id) as total_sales'),
                DB::raw('SUM(total_amount) as total_revenue'),
                DB::raw('SUM(total_tax) as total_tax'),
                DB::raw('AVG(total_amount) as average_sale_value')
            )
            ->first();

        $topProducts = ProductSaleItem::select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('SUM(line_total) as total_revenue')
        )
            ->whereHas('sale', function ($q) use ($fromTs, $toTs, $request) {
                $q->whereBetween('created_at', [$fromTs, $toTs]);
                if ($request->filled('location_id')) {
                    $q->where('location_id', $request->location_id);
                }
            })
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return view('pages.reports.sales_report', compact(
            'sales',
            'products',
            'locations',
            'fromDate',
            'toDate',
            'summary',
            'topProducts'
        ));
    }

    public function transactionReport(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $transactions = InventoryTransaction::with(['product', 'fromLocation', 'toLocation', 'user'])
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($request->filled('product_id'), fn($q) => $q->where('product_id', $request->product_id))
            ->when($request->filled('from_location_id'), fn($q) => $q->where('from_location_id', $request->from_location_id))
            ->when($request->filled('to_location_id'), fn($q) => $q->where('to_location_id', $request->to_location_id))
            ->when($request->filled('transaction_type'), fn($q) => $q->where('transaction_type', $request->transaction_type))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($txn) {
                return $this->addPackBreakdown($txn, $txn->product);
            });

        return view('pages.reports.transaction_report', compact(
            'transactions',
            'products',
            'locations',
            'fromDate',
            'toDate'
        ));
    }

    public function lowStockReport(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;

        $lowStockItems = collect();
        $settings = ProductLocationSetting::with(['product', 'location'])
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->get();

        foreach ($settings as $setting) {
            $currentStock = StockBatch::where('product_id', $setting->product_id)
                ->where('location_id', $setting->location_id)
                ->sum('quantity');

            if ($currentStock <= $setting->reorder_quantity) {
                $product = $setting->product;
                $packSize = $product->default_pack_size ?? 1;
                $packagingType = $product->packaging_type ?? 'unit';
                $unit = $product->unit ?? 'unit';

                if ($packagingType === 'pack') {
                    $fullPacks = floor($currentStock / $packSize);
                    $extraUnits = $currentStock % $packSize;
                    $currentStockPackDisplay = $fullPacks . ' pack' . ($fullPacks != 1 ? 's' : '') .
                        ($extraUnits > 0 ? ' + ' . $extraUnits . ' ' . $unit : '');
                    $reorderDisplay = floor($setting->reorder_quantity / $packSize) . ' pack' .
                        (floor($setting->reorder_quantity / $packSize) != 1 ? 's' : '');
                } else {
                    $currentStockPackDisplay = null;
                    $reorderDisplay = $setting->reorder_quantity . ' ' . $unit;
                }

                $lowStockItems->push((object)[
                    'product' => $product,
                    'location' => $setting->location,
                    'reorder_quantity' => $setting->reorder_quantity,
                    'current_stock' => $currentStock,
                    'current_stock_units' => $currentStock . ' ' . $unit,
                    'current_stock_pack_display' => $currentStockPackDisplay,
                    'reorder_display' => $reorderDisplay,
                    'packaging_type' => $packagingType,
                    'unit' => $unit,
                    'status' => $currentStock == 0 ? 'Out of Stock' : 'Low Stock',
                ]);
            }
        }

        return view('pages.reports.low_stock', compact(
            'lowStockItems',
            'products',
            'locations',
            'productId',
            'locationId'
        ));
    }

    public function expiryReport(Request $request): View
    {
        $products = Product::orderBy('name')->get();
        $locations = Location::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;

        $today = Carbon::today();

        $expiryItems = StockBatch::with(['product', 'location'])
            ->where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->orderBy('expiry_date', 'asc')
            ->get()
            ->filter(function ($batch) {
                $daysRemaining = Carbon::today()->diffInDays($batch->expiry_date, false);
                return $daysRemaining <= 90;
            })
            ->map(function ($batch) {
                $daysRemaining = Carbon::today()->diffInDays($batch->expiry_date, false);
                $batch->days_remaining = $daysRemaining;
                $batch->status_label = $daysRemaining < 0 ? 'EXPIRED' :
                                       ($daysRemaining <= 30 ? 'URGENT' :
                                       ($daysRemaining <= 60 ? 'SOON' : 'OK'));
                $batch->badge_class = $daysRemaining < 0 ? 'danger' :
                                      ($daysRemaining <= 30 ? 'warning' :
                                      ($daysRemaining <= 60 ? 'info' : 'primary'));
                return $this->addPackBreakdown($batch, $batch->product);
            });

        $summary = [
            'expired' => $expiryItems->where('days_remaining', '<', 0)->count(),
            'urgent' => $expiryItems->whereBetween('days_remaining', [0, 30])->count(),
            'soon' => $expiryItems->whereBetween('days_remaining', [31, 60])->count(),
            'ok' => $expiryItems->whereBetween('days_remaining', [61, 90])->count(),
        ];

        return view('pages.reports.expiry_report', compact(
            'expiryItems',
            'products',
            'locations',
            'summary',
            'productId',
            'locationId'
        ));
    }

    /**
     * Helper to add pack breakdown fields to a model object.
     *
     * @param mixed $item
     * @param Product|null $product
     * @return mixed
     */
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