<?php

use App\Http\Controllers\Api\V1\Ride\RideController;
use App\Http\Controllers\Api\V1\Ride\BookingController;
use Illuminate\Support\Facades\Route;

// Rider-facing
Route::get('ride-posts', [RideController::class, 'index']);                 // browse available rides
Route::post('ride-posts/{ridePost}/book', [BookingController::class, 'store']); // book seats
