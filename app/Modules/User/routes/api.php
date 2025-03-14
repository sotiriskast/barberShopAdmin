<?php

use App\Modules\User\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| User Module API Routes
|--------------------------------------------------------------------------
|
| These routes handle the User module functionalities. They are prefixed
| with 'api/users' and protected by the 'api' middleware group.
|
*/

Route::group(['prefix' => 'api/v1', 'middleware' => ['api']], function () {
    // Public user routes
    Route::group(['prefix' => 'users'], function () {
        // These would typically be protected by auth middleware
        // But for initial development, we'll leave them open
        Route::get('/', [UserController::class, 'index']);
        Route::post('/', [UserController::class, 'store']);
        Route::get('/{id}', [UserController::class, 'show']);
        Route::put('/{id}', [UserController::class, 'update']);
        Route::delete('/{id}', [UserController::class, 'destroy']);
    });

    // Protected user routes (will be implemented with Auth module)
    Route::group(['prefix' => 'user', 'middleware' => ['auth:sanctum']], function () {
        // These routes will be added when we implement the Auth module
        // Route::get('/profile', [UserController::class, 'profile']);
        // Route::put('/profile', [UserController::class, 'updateProfile']);
    });
});
