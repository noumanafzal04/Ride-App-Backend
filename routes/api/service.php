<?php

use App\Http\Controllers\Api\V1\Service\AdminServiceProviderController;
use App\Http\Controllers\Api\V1\Service\ServiceBookingController;
use App\Http\Controllers\Api\V1\Service\ServiceController;
use App\Http\Controllers\Api\V1\Service\ServiceProviderController;
use Illuminate\Support\Facades\Route;

// Car services — catalog
Route::get('service-categories', [ServiceController::class, 'categories']);

// Service provider (self)
Route::get('service-provider/me', [ServiceProviderController::class, 'me']);
Route::post('service-provider', [ServiceProviderController::class, 'store']);

// Browse approved providers
Route::get('service-providers', [ServiceProviderController::class, 'index']);
Route::get('service-providers/{id}', [ServiceProviderController::class, 'show'])->whereNumber('id');

// Service bookings — customer
Route::post('service-providers/{providerId}/bookings', [ServiceBookingController::class, 'store'])->whereNumber('providerId');
Route::get('service-bookings', [ServiceBookingController::class, 'index']);
Route::post('service-bookings/{id}/cancel', [ServiceBookingController::class, 'cancel'])->whereNumber('id');
Route::post('service-bookings/{id}/rate', [ServiceBookingController::class, 'rate'])->whereNumber('id');

// Service bookings — provider (guarded in the action)
Route::get('provider/service-bookings', [ServiceBookingController::class, 'providerIndex']);
Route::post('service-bookings/{id}/accept', [ServiceBookingController::class, 'accept'])->whereNumber('id');
Route::post('service-bookings/{id}/reject', [ServiceBookingController::class, 'reject'])->whereNumber('id');
Route::post('service-bookings/{id}/start', [ServiceBookingController::class, 'start'])->whereNumber('id');
Route::post('service-bookings/{id}/complete', [ServiceBookingController::class, 'complete'])->whereNumber('id');

// (Admin provider management moved to the Sanctum admin guard — see routes/api/admin.php)
