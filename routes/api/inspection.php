<?php

use App\Http\Controllers\Api\V1\Inspection\InspectionController;
use Illuminate\Support\Facades\Route;

// ─── Requester (authenticated) ─────────────────────────────
// (Public submit lives in routes/api.php so guests can post.)
Route::get('inspection-requests', [InspectionController::class, 'index']);
Route::get('inspection-requests/{id}', [InspectionController::class, 'show'])->whereNumber('id');
Route::post('inspection-requests/{id}/cancel', [InspectionController::class, 'cancel'])->whereNumber('id');

// (Admin inspection management moved to the Sanctum admin guard — see routes/api/admin.php)
