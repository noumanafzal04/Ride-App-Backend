<?php

use App\Http\Controllers\Api\V1\WorldController;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistanceController;
Route::prefix('v1')->group(function () {
    /**
     * auth route
     * Public
     */
    require __DIR__ . '/api/auth.php';

    Route::middleware('auth:api')->group(function () {
        // Cities
        Route::get('cities', [WorldController::class, 'cities']);
        require __DIR__ . '/api/driver.php';
        require __DIR__ . '/api/vehicle.php';
    });
});



Route::get('/cities',   [CityController::class,    'search']);
Route::get('/distance', [DistanceController::class, 'calculate']);
