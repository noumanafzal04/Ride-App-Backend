<?php

use App\Http\Controllers\Api\V1\Ride\RideController;
use App\Http\Controllers\Api\V1\Ride\BookingController;
use Illuminate\Support\Facades\Route;

// Rider-facing
Route::get('ride-posts', [RideController::class, 'index']);                      // browse available rides
Route::get('ride-posts/new-count', [RideController::class, 'newCount']);         // lightweight poll: count of new rides since last seen
Route::get('ride-posts/{ridePostId}', [RideController::class, 'show']);          // ride detail by id
Route::post('ride-posts/{ridePostId}/book', [BookingController::class, 'store']); // book seats

// Rider's own bookings
Route::get('bookings', [BookingController::class, 'riderIndex']);
Route::post('bookings/{bookingId}/cancel', [BookingController::class, 'cancel']);

// Either party: review after the ride is completed (driver ends the ride)
Route::post('bookings/{bookingId}/rate', [BookingController::class, 'rate']);
