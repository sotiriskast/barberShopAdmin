<?php

use App\Modules\Barber\Controllers\BarberController;
use Illuminate\Support\Facades\Route;

// Group all routes under api/v1 prefix
Route::prefix('api/v1')->group(function () {
    // All routes are prefixed with 'api/v1/barber'
    Route::prefix('barber')->group(function () {
        // All routes are protected by 'auth:sanctum' middleware (applied in RouteServiceProvider)

        // Public barber routes (no role restriction)
        Route::get('/list', [BarberController::class, 'getFilteredBarbers']);

        // Routes protected by 'role:shop_owner,admin' middleware
        Route::middleware('role:shop_owner,admin')->group(function () {
            Route::post('/', [BarberController::class, 'createBarber']);
        });

        // Routes protected by 'role:barber' middleware
        Route::middleware('role:barber')->group(function () {
            // Barber profile routes
            Route::get('/profile', [BarberController::class, 'getProfile']);
            Route::put('/profile', [BarberController::class, 'updateProfile']);

            // Services routes
            Route::get('/services', [BarberController::class, 'getServices']);
            Route::put('/services', [BarberController::class, 'updateServices']);

            // Working hours routes
            Route::get('/working-hours', [BarberController::class, 'getWorkingHours']);
            Route::put('/working-hours', [BarberController::class, 'updateWorkingHours']);

            // Time off routes
            Route::post('/time-off', [BarberController::class, 'addTimeOff']);
            Route::get('/time-off', [BarberController::class, 'getTimeOff']);
            Route::delete('/time-off/{id}', [BarberController::class, 'deleteTimeOff']);

            // Availability routes
            Route::get('/availability', [BarberController::class, 'getAvailability']);
            Route::post('/availability/check', [BarberController::class, 'checkTimeSlot']);
        });

        // Public availability routes (for customer bookings)
        Route::get('/{id}/availability', [BarberController::class, 'getBarberAvailability']);
        Route::post('/availability/check-slot', [BarberController::class, 'checkAvailableTimeSlot']);
    });
});
