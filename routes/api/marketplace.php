<?php

use App\Http\Controllers\Api\V1\Marketplace\CarListingController;
use Illuminate\Support\Facades\Route;

// Buy/Sell car marketplace (auth:api — required by parent group)
Route::get('car-listings', [CarListingController::class, 'index']);          // browse + filters + near
Route::get('car-listings/mine', [CarListingController::class, 'mine']);      // my listings (any status)
Route::get('car-listings/{id}', [CarListingController::class, 'show'])->whereNumber('id');
Route::post('car-listings', [CarListingController::class, 'store']);         // create (multipart images)
Route::put('car-listings/{id}', [CarListingController::class, 'update'])->whereNumber('id');
Route::patch('car-listings/{id}/sold', [CarListingController::class, 'markSold'])->whereNumber('id');
Route::delete('car-listings/{id}', [CarListingController::class, 'destroy'])->whereNumber('id');
