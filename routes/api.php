<?php

use App\Http\Controllers\Api\V1\WorldController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\CityController;
use App\Http\Controllers\Api\DistanceController;
use App\Http\Controllers\Api\V1\Inspection\InspectionController;
Route::prefix('v1')->group(function () {
    /**
     * auth route
     * Public
     */
    require __DIR__ . '/api/auth.php';

    // Car inspection — public submit (guests + logged-in; user attached if a token is sent)
    Route::post('inspection-requests', [InspectionController::class, 'store']);
    // Public status lookup by tracking code (guests, no auth)
    Route::get('inspection-requests/track/{token}', [InspectionController::class, 'track']);

    Route::middleware('auth:api')->group(function () {
        // Cities
        Route::get('cities', [WorldController::class, 'cities']);

        // Reverb private-channel authorization via the Passport token
        // (Echo authEndpoint → /api/v1/broadcasting/auth)
        Route::post('broadcasting/auth', fn (Request $request) => Broadcast::auth($request));

        require __DIR__ . '/api/driver.php';
        require __DIR__ . '/api/vehicle.php';
        require __DIR__ . '/api/ride.php';
        require __DIR__ . '/api/notification.php';
        require __DIR__ . '/api/inspection.php';
        require __DIR__ . '/api/chat.php';
        require __DIR__ . '/api/service.php';
    });
});



Route::get('/cities',   [CityController::class,    'search']);
Route::get('/distance', [DistanceController::class, 'calculate']);
