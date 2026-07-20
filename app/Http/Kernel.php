<?php

namespace App\Http;

use Illuminate\Foundation\Http\Kernel as HttpKernel;

class Kernel extends HttpKernel
{
    protected $middleware = [
        \App\Http\Middleware\TrustProxies::class,
        \Illuminate\Http\Middleware\HandleCors::class,
        \App\Http\Middleware\PreventRequestsDuringMaintenance::class,
        \Illuminate\Foundation\Http\Middleware\ValidatePostSize::class,
        \App\Http\Middleware\TrimStrings::class,
        \Illuminate\Foundation\Http\Middleware\ConvertEmptyStringsToNull::class,
    ];

    protected $middlewareGroups = [
        'web' => [
            \App\Http\Middleware\EncryptCookies::class,
            \Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse::class,
            \Illuminate\Session\Middleware\StartSession::class,
            \Illuminate\View\Middleware\ShareErrorsFromSession::class,
            \App\Http\Middleware\VerifyCsrfToken::class,
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
            \App\Http\Middleware\SetLocale::class,
        ],

        'api' => [
            'throttle:api',
            \Illuminate\Routing\Middleware\SubstituteBindings::class,
        ],
    ];

    protected $routeMiddleware = [
        'auth' => \App\Http\Middleware\Authenticate::class,
        'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
        'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
        'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
        'can' => \Illuminate\Auth\Middleware\Authorize::class,
        'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
        'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
        'signed' => \App\Http\Middleware\ValidateSignature::class,
        'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
        'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,

        'isSetRole' => \App\Http\Middleware\PermissionMiddleware::class,
        'manage_user' => \App\Http\Middleware\UserMiddleware::class,
        'manage_locations' => \App\Http\Middleware\LocationMiddleware::class,
        'manage_products' => \App\Http\Middleware\ProductMiddleware::class,
        'manage_supplier' => \App\Http\Middleware\SupplierMiddleware::class,
        'manage_goods_receipt' => \App\Http\Middleware\ManageGoodsReceiptMiddleware::class,
        'manage_inventory_transfer' => \App\Http\Middleware\ManageInventoryTransferMiddleware::class,
        'manage_inventory_adjustment' => \App\Http\Middleware\ManageInventoryAdjustmentMiddleware::class,
        'manage_patients' => \App\Http\Middleware\ManagePatientsMiddleware::class,
        'manage_treatment_consumption' => \App\Http\Middleware\ManageTreatmentConsumptionMiddleware::class,
        'manage_product_sales' => \App\Http\Middleware\ManageProductSalesMiddleware::class,
        'view_reports' => \App\Http\Middleware\ViewReportsMiddleware::class,
    ];
}