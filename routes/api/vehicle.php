<?php

use App\Http\Controllers\Api\V1\Vehicle\VehicleMakeModelController;
use Illuminate\Support\Facades\Route;

Route::prefix('vehicle')->group(function () {
    Route::get('makes', [VehicleMakeModelController::class, 'makes']);
    Route::get('models', [VehicleMakeModelController::class, 'models']);
});
