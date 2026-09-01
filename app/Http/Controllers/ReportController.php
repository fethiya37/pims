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
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ReportController extends Controller
{
    private function getAllowedLocationIds()
    {
        $user = Auth::user();
        if ($user->role && $user->role->role_name === 'Super Admin') {
            return Location::pluck('id')->toArray();
        }
        return [$user->location_id];
    }

    private function getLocationsForDropdown()
    {
        $ids = $this->getAllowedLocationIds();
        return Location::whereIn('id', $ids)->orderBy('name')->get();
    }

    public function stockBalance(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $products = Product::orderBy('name')->get();
        $activeTab = $request->active_tab ?? 'overall';
        
        $productId = null;
        $locationId = null;
        $overallBalances = collect();
        $locationBalances = collect();
        $batchBalances = collect();

        return view('pages.reports.stock_balance', compact(
            'products',
            'activeTab',
            'allowedLocations',
            'productId',
            'locationId',
            'overallBalances',
            'locationBalances',
            'batchBalances'
        ));
    }

    public function stockBalanceOverall(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;
        $activeTab = 'overall';

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        } else {
            if (!empty($locationId) && !in_array($locationId, $allowedLocationIds)) {
                $locationId = null;
            }
        }

        $overallBalances = collect();
        $locationBalances = collect();
        $batchBalances = collect();

        $overallQuery = StockBatch::select(
            'product_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('MAX(updated_at) as last_updated')
        )
            ->whereIn('location_id', $allowedLocationIds)
            ->when(!empty($productId), fn($q) => $q->where('product_id', $productId))
            ->when(!empty($locationId), fn($q) => $q->where('location_id', $locationId))
            ->groupBy('product_id')
            ->with('product')
            ->orderBy('last_updated', 'desc');

        $overallBalances = $overallQuery->get()->map(function ($item) {
            return $this->addPackBreakdown($item, $item->product);
        });

        return view('pages.reports.stock_balance', compact(
            'products',
            'overallBalances',
            'locationBalances',
            'batchBalances',
            'activeTab',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function stockBalanceLocation(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;
        $activeTab = 'location';

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        } else {
            if (!empty($locationId) && !in_array($locationId, $allowedLocationIds)) {
                $locationId = null;
            }
        }

        $overallBalances = collect();
        $locationBalances = collect();
        $batchBalances = collect();

        $locationQuery = StockBatch::select(
            'product_id',
            'location_id',
            DB::raw('SUM(quantity) as total_quantity'),
            DB::raw('MAX(updated_at) as last_updated')
        )
            ->whereIn('location_id', $allowedLocationIds)
            ->when(!empty($productId), fn($q) => $q->where('product_id', $productId))
            ->when(!empty($locationId), fn($q) => $q->where('location_id', $locationId))
            ->groupBy('product_id', 'location_id')
            ->with(['product', 'location'])
            ->orderBy('last_updated', 'desc');

        $locationBalances = $locationQuery->get()->map(function ($item) {
            return $this->addPackBreakdown($item, $item->product);
        });

        return view('pages.reports.stock_balance', compact(
            'products',
            'overallBalances',
            'locationBalances',
            'batchBalances',
            'activeTab',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function stockBalanceBatch(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();
        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;
        $activeTab = 'batch';

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        } else {
            if (!empty($locationId) && !in_array($locationId, $allowedLocationIds)) {
                $locationId = null;
            }
        }

        $overallBalances = collect();
        $locationBalances = collect();
        $batchBalances = collect();

        $query = StockBatch::with(['product', 'location'])
            ->whereIn('location_id', $allowedLocationIds)
            ->when(!empty($productId), fn($q) => $q->where('product_id', $productId))
            ->when(!empty($locationId), fn($q) => $q->where('location_id', $locationId))
            ->orderBy('updated_at', 'desc');

        $batchBalances = $query->get()->map(function ($batch) {
            return $this->addPackBreakdown($batch, $batch->product);
        });

        return view('pages.reports.stock_balance', compact(
            'products',
            'overallBalances',
            'locationBalances',
            'batchBalances',
            'activeTab',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function interLocationTransfer(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $fromLocationId = $request->from_location_id;
        $toLocationId = $request->to_location_id;
        $productId = $request->product_id;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (!empty($fromLocationId) && !in_array($fromLocationId, $allowedLocationIds)) {
                $fromLocationId = null;
            }
            if (!empty($toLocationId) && !in_array($toLocationId, $allowedLocationIds)) {
                $toLocationId = null;
            }
        }

        $transfers = InventoryTransaction::with(['product', 'fromLocation', 'toLocation', 'user'])
            ->where('transaction_type', 'transfer')
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->where(function ($query) use ($allowedLocationIds) {
                $query->whereIn('from_location_id', $allowedLocationIds)
                    ->orWhereIn('to_location_id', $allowedLocationIds);
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($fromLocationId, fn($q) => $q->where('from_location_id', $fromLocationId))
            ->when($toLocationId, fn($q) => $q->where('to_location_id', $toLocationId))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($txn) {
                return $this->addPackBreakdown($txn, $txn->product);
            });

        return view('pages.reports.inter_location_transfer', compact(
            'transfers',
            'products',
            'fromDate',
            'toDate',
            'fromLocationId',
            'toLocationId',
            'allowedLocations'
        ));
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

        $locationId = $request->location_id;
        $patientId = $request->patient_id;
        $productId = $request->product_id;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        }

        $consumptions = TreatmentConsumption::with(['patient', 'location', 'doctor', 'items.product'])
            ->whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($patientId, fn($q) => $q->where('patient_id', $patientId))
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($productId, function ($q) use ($productId) {
                $q->whereHas('items', fn($sub) => $sub->where('product_id', $productId));
            })
            ->orderByDesc('created_at')
            ->get();

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
            'consumptions',
            'products',
            'patients',
            'fromDate',
            'toDate',
            'summary',
            'locationId',
            'allowedLocations'
        ));
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

        $locationId = $request->location_id;
        $productId = $request->product_id;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        }

        $sales = ProductSale::with(['location', 'user', 'items.product'])
            ->whereIn('location_id', $allowedLocationIds)
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->when($locationId, fn($q) => $q->where('location_id', $locationId))
            ->when($productId, function ($q) use ($productId) {
                $q->whereHas('items', fn($sub) => $sub->where('product_id', $productId));
            })
            ->orderByDesc('created_at')
            ->get();

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

        $topProducts = ProductSaleItem::select(
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
            ->groupBy('product_id')
            ->with('product')
            ->orderByDesc('total_revenue')
            ->limit(10)
            ->get();

        return view('pages.reports.sales_report', compact(
            'sales',
            'products',
            'fromDate',
            'toDate',
            'summary',
            'topProducts',
            'locationId',
            'allowedLocations'
        ));
    }

    public function transactionReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $fromDate = $request->from_date ?? now()->subMonth()->format('Y-m-d');
        $toDate = $request->to_date ?? now()->format('Y-m-d');

        $fromTs = Carbon::parse($fromDate)->startOfDay();
        $toTs = Carbon::parse($toDate)->addDay()->startOfDay();

        $fromLocationId = $request->from_location_id;
        $toLocationId = $request->to_location_id;
        $productId = $request->product_id;
        $transactionType = $request->transaction_type;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (!empty($fromLocationId) && !in_array($fromLocationId, $allowedLocationIds)) {
                $fromLocationId = null;
            }
            if (!empty($toLocationId) && !in_array($toLocationId, $allowedLocationIds)) {
                $toLocationId = null;
            }
        }

        $transactions = InventoryTransaction::with(['product', 'fromLocation', 'toLocation', 'user'])
            ->whereBetween('created_at', [$fromTs, $toTs])
            ->where(function ($query) use ($allowedLocationIds) {
                $query->whereIn('from_location_id', $allowedLocationIds)
                    ->orWhereIn('to_location_id', $allowedLocationIds);
            })
            ->when($productId, fn($q) => $q->where('product_id', $productId))
            ->when($fromLocationId, fn($q) => $q->where('from_location_id', $fromLocationId))
            ->when($toLocationId, fn($q) => $q->where('to_location_id', $toLocationId))
            ->when($transactionType, fn($q) => $q->where('transaction_type', $transactionType))
            ->orderByDesc('created_at')
            ->get()
            ->map(function ($txn) {
                return $this->addPackBreakdown($txn, $txn->product);
            });

        return view('pages.reports.transaction_report', compact(
            'transactions',
            'products',
            'fromDate',
            'toDate',
            'fromLocationId',
            'toLocationId',
            'transactionType',
            'allowedLocations'
        ));
    }

    public function lowStockReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        }

        $lowStockItems = collect();
        $settings = ProductLocationSetting::with(['product', 'location'])
            ->whereIn('location_id', $allowedLocationIds)
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
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function expiryReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $productId = $request->product_id;
        $locationId = $request->location_id;

        if (!Auth::user()->role || Auth::user()->role->role_name !== 'Super Admin') {
            if (empty($locationId)) {
                $locationId = Auth::user()->location_id;
            } else {
                if (!in_array($locationId, $allowedLocationIds)) {
                    $locationId = Auth::user()->location_id;
                }
            }
        }

        $today = Carbon::today();

        $expiryItems = StockBatch::with(['product', 'location'])
            ->whereIn('location_id', $allowedLocationIds)
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
            'summary',
            'productId',
            'locationId',
            'allowedLocations'
        ));
    }

    public function zeroStockReport(Request $request): View
    {
        $allowedLocations = $this->getLocationsForDropdown();
        $allowedLocationIds = $allowedLocations->pluck('id')->toArray();

        $products = Product::orderBy('name')->get();

        $zeroStockItems = collect();

        foreach ($products as $product) {
            $totalStock = StockBatch::where('product_id', $product->id)
                ->whereIn('location_id', $allowedLocationIds)
                ->sum('quantity');

            if ($totalStock <= 0) {
                $packSize = $product->default_pack_size ?? 1;
                $packagingType = $product->packaging_type ?? 'unit';
                $unit = $product->unit ?? 'unit';

                $zeroStockItems->push((object)[
                    'product' => $product,
                    'total_stock' => $totalStock,
                    'current_stock_units' => $totalStock . ' ' . $unit,
                    'packaging_type' => $packagingType,
                    'unit' => $unit,
                    'pack_size' => $packSize,
                    'status' => 'Out of Stock',
                ]);
            }
        }

        $totalProducts = $products->count();
        $totalZeroStock = $zeroStockItems->count();

        return view('pages.reports.zero_stock', compact(
            'zeroStockItems',
            'totalProducts',
            'totalZeroStock'
        ));
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