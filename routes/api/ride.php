<?php

use App\Http\Controllers\Api\V1\Ride\RideController;
use App\Http\Controllers\Api\V1\Ride\BookingController;
use App\Http\Controllers\Api\V1\Ride\RideAlertController;
use App\Http\Controllers\Api\V1\Driver\DriverPublicController;
use Illuminate\Support\Facades\Route;

// Public driver data for the ride-detail screen
Route::get('drivers/{driverId}/summary', [DriverPublicController::class, 'summary']); // aggregate stats
Route::get('drivers/{driverId}/reviews', [DriverPublicController::class, 'reviews']); // paginated
Route::get('drivers/{driverId}/trips', [DriverPublicController::class, 'trips']);     // paginated

// Rider "notify me" alerts (static paths before ride-posts/{id})
Route::get('ride-alerts', [RideAlertController::class, 'index']);
Route::post('ride-alerts', [RideAlertController::class, 'store']);
Route::delete('ride-alerts/{id}', [RideAlertController::class, 'destroy']);

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
