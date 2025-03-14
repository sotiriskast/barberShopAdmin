<?php

use App\Modules\Shop\Controllers\CustomerShopController;
use App\Modules\Shop\Controllers\ShopController;
use App\Modules\Shop\Controllers\ShopOwnerController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Shop Module Routes
|--------------------------------------------------------------------------
*/

// Public routes
Route::prefix('api/v1')->group(function () {
    Route::get('shops', [ShopController::class, 'index']);
    Route::get('shops/{id}', [ShopController::class, 'show']);
});

// Customer-specific routes
Route::middleware(['auth:sanctum', 'role:customer'])->prefix('api/v1/customer')->group(function () {
    Route::get('shops', [CustomerShopController::class, 'index']);
    Route::get('shops/{id}', [CustomerShopController::class, 'show']);
    Route::get('shops/{id}/barbers', [CustomerShopController::class, 'getBarbers']);
    Route::get('shops/{id}/services', [CustomerShopController::class, 'getServices']);
});

// Shop owner routes
Route::middleware(['auth:sanctum', 'role:shop_owner'])->prefix('api/v1/shop-owner')->group(function () {
    Route::get('shops', [ShopOwnerController::class, 'index']);
    Route::post('shops', [ShopOwnerController::class, 'store']);
    Route::get('shops/{id}', [ShopOwnerController::class, 'show']);
    Route::put('shops/{id}', [ShopOwnerController::class, 'update']);
    Route::delete('shops/{id}', [ShopOwnerController::class, 'destroy']);
});
