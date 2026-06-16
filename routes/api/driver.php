<?php

use App\Http\Controllers\Api\V1\Driver\DriverProfileController;
use App\Http\Controllers\Api\V1\Driver\RidePostController;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->group(function () {
    Route::post('onboard', [DriverProfileController::class, 'onboard']);
    // ride posts
    Route::apiResource('ride-posts', RidePostController::class);
});
