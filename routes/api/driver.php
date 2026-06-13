<?php

use App\Http\Controllers\Api\V1\Driver\DriverProfileController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:api')->prefix('driver')->group(function () {
    Route::post('onboard', [DriverProfileController::class, 'onboard']);
});
