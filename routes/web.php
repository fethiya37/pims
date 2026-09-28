<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GoodsReceiptController;
use App\Http\Controllers\InventoryTransferController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\PatientController;
use App\Http\Controllers\ProductSaleController;
use App\Http\Controllers\TreatmentConsumptionController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\IndexController;
use App\Http\Controllers\InventoryAdjustmentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SupplierController;

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/', [IndexController::class, 'index'])->name('dashboard');
    Route::get('/dashboard/data', [DashboardController::class, 'data'])->name('dashboard.data');
    Route::get('/dashboard/report', [DashboardController::class, 'report'])->name('dashboard.report');
});

Route::controller(LocationController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_locations'])->group(function () {
        Route::get('/location', 'index');
        Route::post('/add-location', 'addLocation');
        Route::post('/edit-location-{id}', 'editLocation');
        Route::get('delete-location-{id}', 'deleteLocation');
    });

Route::controller(ProductController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_products'])->group(function () {
        Route::get('/products', 'index')->name('products.index');
        Route::get('/products/data', 'getProductData')->name('products.data');
        Route::get('/edit-product-form-{id}', 'editProductForm');
        Route::post('/add-product', 'addProduct');
        Route::post('/edit-product-{id}', 'editProduct');
        Route::get('delete-product-{id}', 'deleteProduct');
        Route::get('/products/{product}/opening-quantities', 'openingQuantities')->name('products.opening-quantities');
        Route::post('/products/{product}/opening-quantities', 'storeOpeningQuantities')->name('products.opening-quantities.store');
        Route::delete('/opening-quantities/{id}', 'destroyOpeningQuantity')->name('products.opening-quantities.destroy');
        Route::get('/products/{product}/reorder-settings', 'reorderSettings')->name('products.reorder-settings');
        Route::post('/products/{product}/reorder-settings', 'storeReorderSettings')->name('products.reorder-settings.store');
    });

Route::controller(GoodsReceiptController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_goods_receipt'])->group(function () {
        Route::get('/goods-receipts', 'index')->name('goods-receipts.index');
        Route::get('/goods-receipts/data', 'getData')->name('goods-receipts.data');
        Route::get('/goods-receipts/{id}/view', 'view')->name('goods-receipts.view');
        Route::get('/goods-receipts/{id}/edit-form', 'editForm')->name('goods-receipts.edit-form');
        Route::post('/goods-receipts', 'store')->name('goods-receipts.store');
        Route::put('/goods-receipts/{id}', 'update')->name('goods-receipts.update');
        Route::get('/goods-receipts/{id}/receive', 'receive')->name('goods-receipts.receive');
        Route::delete('/goods-receipts/{id}', 'destroy')->name('goods-receipts.destroy');
    });

Route::controller(SupplierController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_supplier'])->group(function () {
        Route::get('/suppliers', 'index')->name('suppliers.index');
        Route::post('/suppliers', 'store')->name('suppliers.store');
        Route::post('/suppliers/{supplier}', 'update')->name('suppliers.update');
        Route::delete('/suppliers/{id}', 'destroy')->name('suppliers.destroy');
    });

Route::controller(InventoryTransferController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_inventory_transfer'])->group(function () {
        Route::get('/inventory-transfers', 'index')->name('inventory-transfers.index');
        Route::get('/inventory-transfers/data', 'getData')->name('inventory-transfers.data');
        Route::get('/inventory-transfers/{id}/view', 'view')->name('inventory-transfers.view');
        Route::get('/inventory-transfers/{id}/edit-form', 'editForm')->name('inventory-transfers.edit-form');
        Route::post('/inventory-transfers', 'store')->name('inventory-transfers.store');
        Route::put('/inventory-transfers/{id}', 'update')->name('inventory-transfers.update');
        Route::get('/inventory-transfers/{id}/approve', 'approve')->name('inventory-transfers.approve');
        Route::post('/inventory-transfers/{id}/reject', 'reject')->name('inventory-transfers.reject');
        Route::get('/inventory-transfers/{id}/issue', 'issue')->name('inventory-transfers.issue');
        Route::get('/inventory-transfers/{id}/receive', 'receive')->name('inventory-transfers.receive');
        Route::delete('/inventory-transfers/{id}', 'destroy')->name('inventory-transfers.destroy');
    });

Route::controller(InventoryAdjustmentController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_inventory_adjustment'])->group(function () {
        Route::get('/inventory-adjustments', 'index')->name('inventory-adjustments.index');
        Route::get('/inventory-adjustments/data', 'getData')->name('inventory-adjustments.data');
        Route::post('/inventory-adjustments', 'store')->name('inventory-adjustments.store');
    });

Route::controller(CategoryController::class)
    ->middleware(['auth', 'verified', 'isSetRole'])->group(function () {
        Route::post('/add-category', 'addCategory')->name('add-category');
        Route::post('/edit-category-{id}', 'editCategory')->name('edit-category');
        Route::get('delete-category-{id}', 'deleteCategory')->name('delete-category');
    });

Route::controller(PatientController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_patients'])->group(function () {
        Route::get('/patients', 'index')->name('patients.index');
        Route::post('/patients', 'store')->name('patients.store');
        Route::get('/patients/{id}/edit', 'edit')->name('patients.edit');
        Route::put('/patients/{id}', 'update')->name('patients.update');
        Route::delete('/patients/{id}', 'destroy')->name('patients.destroy');
    });

Route::controller(TreatmentConsumptionController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_treatment_consumption'])->group(function () {
        Route::get('/treatments', 'index')->name('treatments.index');
        Route::get('/treatments/data', 'getData')->name('treatments.data');
        Route::get('/treatments/{id}/view', 'view')->name('treatments.view');
        Route::get('/treatments/{id}/edit-form', 'editForm')->name('treatments.edit-form');
        Route::post('/treatments', 'store')->name('treatments.store');
        Route::put('/treatments/{id}', 'update')->name('treatments.update');
        Route::get('/treatments/{id}/complete', 'complete')->name('treatments.complete');
        Route::delete('/treatments/{id}', 'destroy')->name('treatments.destroy');
    });

Route::controller(ProductSaleController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_product_sales'])->group(function () {
        Route::get('/sales', 'index')->name('sales.index');
        Route::get('/sales/data', 'getData')->name('sales.data');
        Route::get('/sales/{id}/view', 'view')->name('sales.view');
        Route::get('/sales/{id}/edit-form', 'editForm')->name('sales.edit-form');
        Route::post('/sales', 'store')->name('sales.store');
        Route::put('/sales/{id}', 'update')->name('sales.update');
        Route::get('/sales/{id}/complete', 'complete')->name('sales.complete');
        Route::delete('/sales/{id}', 'destroy')->name('sales.destroy');
    });

    
Route::controller(UserController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_user'])->group(function () {
        Route::get('/users', 'index');
        Route::post('/add-user', 'addUser');
        Route::post('/editUser-{id}', 'editUser');
        Route::get('/delete-user-{id}', 'deleteUser');
        Route::post('/toggle-active-{id}', 'toggleActive');
    });

Route::controller(RoleController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'manage_user'])->group(function () {
        Route::get('/roles', 'index')->name('roles.index');
        Route::post('/add-role', 'addRole');
        Route::post('/edit-role-{id}', 'editRole');
        Route::get('/delete-role-{id}', 'deleteRole');
        Route::post('/set-role-{id}', 'setRole');
    });

Route::controller(ReportController::class)
    ->middleware(['auth', 'verified', 'isSetRole', 'view_reports'])->group(function () {
        Route::get('/stock-balance', 'stockBalance')->name('reports.stock-balance');
        Route::get('/stock-balance/overall', 'stockBalanceOverall')->name('reports.stock-balance.overall');
        Route::get('/stock-balance/location', 'stockBalanceLocation')->name('reports.stock-balance.location');
        Route::get('/stock-balance/batch', 'stockBalanceBatch')->name('reports.stock-balance.batch');
        Route::get('/stock-balance/data', 'getStockBalanceData')->name('reports.stock-balance.data');

        Route::get('/inter-location-transfer', 'interLocationTransfer')->name('reports.inter-location-transfer');
        Route::get('/inter-location-transfer/data', 'getInterTransferData')->name('reports.inter-location-transfer.data');

        Route::get('/treatment-report', 'treatmentConsumption')->name('reports.treatment-consumption');
        Route::get('/treatment-report/data', 'getTreatmentData')->name('reports.treatment-consumption.data');

        Route::get('/sales-report', 'salesReport')->name('reports.sales-report');
        Route::get('/sales-report/data', 'getSalesReportData')->name('reports.sales-report.data');
        Route::get('/sales-report/top-data', 'getTopProductsData')->name('reports.sales-report.top-data');

        Route::get('/transaction-report', 'transactionReport')->name('reports.transaction');
        Route::get('/transaction-report/data', 'getTransactionData')->name('reports.transaction.data');

        Route::get('/low-stock', 'lowStockReport')->name('reports.low-stock');
        Route::get('/low-stock/data', 'getLowStockData')->name('reports.low-stock.data');

        Route::get('/expiry-report', 'expiryReport')->name('reports.expiry');
        Route::get('/expiry-report/data', 'getExpiryData')->name('reports.expiry.data');

        Route::get('/zero-stock-report', 'zeroStockReport')->name('reports.zero-stock');
        Route::get('/zero-stock-report/data', 'getZeroStockData')->name('reports.zero-stock.data');
    });

Route::prefix('notifications')->name('notifications.')->middleware('auth')->group(function () {
    Route::get('/fetch', [NotificationController::class, 'fetch'])->name('fetch');
    Route::post('/{id}/mark-read', [NotificationController::class, 'markRead'])->name('mark-read');
    Route::post('/mark-all-read', [NotificationController::class, 'markAllRead'])->name('mark-all-read');
});

Route::get('lang/{locale}', function ($locale) {
    if (in_array($locale, ['en', 'am'])) {
        session(['locale' => $locale]);
        app()->setLocale($locale);
    }
    return redirect()->back();
});

require __DIR__ . '/auth.php';