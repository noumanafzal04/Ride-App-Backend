<?php

use App\Http\Controllers\Api\V1\Rental\RentalCarController;
use App\Http\Controllers\Api\V1\Rental\RentalBookingController;
use Illuminate\Support\Facades\Route;

// Rent a Car (auth:api — required by parent group)
Route::get('rentals', [RentalCarController::class, 'index']);                 // browse + filters + near
Route::get('rentals/mine', [RentalCarController::class, 'mine']);             // my rental cars
Route::get('rentals/bookings/mine', [RentalBookingController::class, 'mine']);        // my rental bookings (as customer)
Route::get('rentals/bookings/owner', [RentalBookingController::class, 'ownerBookings']); // bookings on my cars
Route::get('rentals/{id}', [RentalCarController::class, 'show'])->whereNumber('id');
Route::post('rentals', [RentalCarController::class, 'store']);                 // list a car (multipart)
Route::put('rentals/{id}', [RentalCarController::class, 'update'])->whereNumber('id');
Route::patch('rentals/{id}/status', [RentalCarController::class, 'setStatus'])->whereNumber('id');
Route::delete('rentals/{id}', [RentalCarController::class, 'destroy'])->whereNumber('id');

// Bookings
Route::post('rentals/{rentalId}/book', [RentalBookingController::class, 'store'])->whereNumber('rentalId');
Route::patch('rentals/bookings/{id}/cancel', [RentalBookingController::class, 'cancel'])->whereNumber('id');
Route::patch('rentals/bookings/{id}/{action}', [RentalBookingController::class, 'action'])
    ->whereNumber('id')->whereIn('action', ['accept', 'reject', 'start', 'complete']);
