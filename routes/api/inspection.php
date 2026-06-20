<?php

use App\Http\Controllers\Api\V1\Inspection\AdminInspectionController;
use App\Http\Controllers\Api\V1\Inspection\InspectionController;
use Illuminate\Support\Facades\Route;

// ─── Requester (authenticated) ─────────────────────────────
// (Public submit lives in routes/api.php so guests can post.)
Route::get('inspection-requests', [InspectionController::class, 'index']);
Route::get('inspection-requests/{id}', [InspectionController::class, 'show'])->whereNumber('id');
Route::post('inspection-requests/{id}/cancel', [InspectionController::class, 'cancel'])->whereNumber('id');

// ─── Admin / team (auth:api + admin) ───────────────────────
Route::prefix('admin')->middleware('admin')->group(function () {
    Route::get('inspection-categories', [AdminInspectionController::class, 'categories']);
    Route::get('inspection-requests', [AdminInspectionController::class, 'index']);
    Route::get('inspection-requests/{id}', [AdminInspectionController::class, 'show'])->whereNumber('id');
    Route::post('inspection-requests/{id}/assign', [AdminInspectionController::class, 'assign'])->whereNumber('id');
    Route::post('inspection-requests/{id}/status', [AdminInspectionController::class, 'updateStatus'])->whereNumber('id');
    Route::post('inspection-requests/{id}/report', [AdminInspectionController::class, 'saveReport'])->whereNumber('id');
});
