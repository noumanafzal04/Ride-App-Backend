<?php

use App\Http\Controllers\Api\V1\Driver\DriverProfileController;
use App\Http\Controllers\Api\V1\Driver\RidePostController;
use App\Http\Controllers\Api\V1\Ride\BookingController;
use Illuminate\Support\Facades\Route;

Route::prefix('driver')->group(function () {
    Route::post('onboard', [DriverProfileController::class, 'onboard']);
    // ride posts
    Route::apiResource('ride-posts', RidePostController::class);

    // bookings received on the driver's posts
    Route::get('bookings', [BookingController::class, 'driverIndex']);
    Route::post('bookings/{booking}/accept', [BookingController::class, 'accept']);
    Route::post('bookings/{booking}/reject', [BookingController::class, 'reject']);
});
