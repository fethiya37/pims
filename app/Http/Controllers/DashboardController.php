<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Models\Product;
use App\Models\Category;
use App\Models\User;
use App\Models\Patient;
use App\Models\TreatmentConsumption;
use App\Models\ProductSale;
use App\Models\Location;
use App\Models\StockBatch;
use App\Models\ProductLocationSetting;

class DashboardController extends Controller
{
    public function data(Request $request)
    {
        try {
            $today = Carbon::today();

            $counts = [
                'products' => (int) Product::count(),
                'categories' => (int) Category::count(),
                'locations' => (int) Location::count(),
                'users' => (int) User::count(),
                'patients' => (int) Patient::count(),
                'treatments' => (int) TreatmentConsumption::where('status', 'completed')->count(),
                'sales' => (int) ProductSale::where('status', 'completed')->count(),
                'batches' => (int) StockBatch::where('quantity', '>', 0)->count(),
                'active_products' => (int) Product::where('status', 'active')->count(),
                'low_stock' => $this->getLowStockCount(),
                'expired' => $this->getExpiredCount(),
                'expiring_soon' => $this->getExpiringSoonCount(),
            ];

            return response()->json([
                'ok' => true,
                'counts' => $counts,
            ]);

        } catch (\Throwable $e) {
            Log::error('dashboard.data failed', [
                'msg' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'ok' => false,
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function report()
    {
        $fileName = 'dashboard_report_' . now()->format('Ymd_His') . '.csv';
        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
        ];

        $data = [
            ['Metric', 'Value'],
            ['Products', Product::count()],
            ['Active Products', Product::where('status', 'active')->count()],
            ['Categories', Category::count()],
            ['Locations', Location::count()],
            ['Users', User::count()],
            ['Patients', Patient::count()],
            ['Completed Treatments', TreatmentConsumption::where('status', 'completed')->count()],
            ['Completed Sales', ProductSale::where('status', 'completed')->count()],
            ['Stock Batches', StockBatch::where('quantity', '>', 0)->count()],
            ['Low Stock Items', $this->getLowStockCount()],
            ['Expired Stock Items', $this->getExpiredCount()],
            ['Expiring Soon Items', $this->getExpiringSoonCount()],
        ];

        $callback = function () use ($data) {
            $handle = fopen('php://output', 'w');
            foreach ($data as $row) {
                fputcsv($handle, $row);
            }
            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getLowStockCount()
    {
        $count = 0;
        $settings = ProductLocationSetting::with(['product', 'location'])->get();

        foreach ($settings as $setting) {
            $currentStock = StockBatch::where('product_id', $setting->product_id)
                ->where('location_id', $setting->location_id)
                ->sum('quantity');

            if ($currentStock <= $setting->reorder_quantity) {
                $count++;
            }
        }

        return $count;
    }

    private function getExpiredCount()
    {
        return StockBatch::where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '<', Carbon::today())
            ->count();
    }

    private function getExpiringSoonCount()
    {
        return StockBatch::where('quantity', '>', 0)
            ->whereNotNull('expiry_date')
            ->where('expiry_date', '>=', Carbon::today())
            ->where('expiry_date', '<=', Carbon::today()->addDays(30))
            ->count();
    }
}