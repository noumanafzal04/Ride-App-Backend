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
    Route::post('bookings/{bookingId}/accept', [BookingController::class, 'accept']);
    Route::post('bookings/{bookingId}/reject', [BookingController::class, 'reject']);

    // ride trip lifecycle (driver controls the whole ride)
    Route::post('ride-posts/{ridePostId}/start', [BookingController::class, 'startRide']);
    Route::post('ride-posts/{ridePostId}/end', [BookingController::class, 'endRide']);
});
