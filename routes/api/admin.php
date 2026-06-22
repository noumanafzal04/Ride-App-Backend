<?php

use App\Http\Controllers\Api\V1\Admin\AuthController;
use App\Http\Controllers\Api\V1\Admin\NotificationController;
use App\Http\Controllers\Api\V1\Admin\ReportController;
use App\Http\Controllers\Api\V1\Admin\RoleController;
use App\Http\Controllers\Api\V1\Admin\StaffController;
use App\Http\Controllers\Api\V1\Admin\ServiceCategoryController;
use App\Http\Controllers\Api\V1\Admin\UserController;
use App\Http\Controllers\Api\V1\Inspection\AdminInspectionController;
use App\Http\Controllers\Api\V1\Service\AdminServiceProviderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\Route;

// Admin panel — separate admin_users via Sanctum, gated by module permissions.
Route::prefix('admin')->group(function () {
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('auth/me', [AuthController::class, 'me']);
        Route::post('auth/logout', [AuthController::class, 'logout']);

        // Reverb private-channel auth for the admin SPA (Sanctum-guarded)
        Route::post('broadcasting/auth', fn (Request $request) => Broadcast::auth($request));

        // Admin activity feed (any admin)
        Route::get('notifications', [NotificationController::class, 'index']);
        Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
        Route::post('notifications/read', [NotificationController::class, 'markRead']);

        // Roles & permissions
        Route::get('permissions', [RoleController::class, 'permissions'])->middleware('permission:roles.view');
        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
        Route::get('roles/{id}', [RoleController::class, 'show'])->whereNumber('id')->middleware('permission:roles.view');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::put('roles/{id}', [RoleController::class, 'update'])->whereNumber('id')->middleware('permission:roles.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->whereNumber('id')->middleware('permission:roles.delete');

        // App users + profile verification
        Route::get('app-users', [UserController::class, 'index'])->middleware('permission:users.view');
        Route::get('app-users/{id}', [UserController::class, 'show'])->whereNumber('id')->middleware('permission:users.view');
        Route::post('app-users/{id}/verification', [UserController::class, 'setVerification'])->whereNumber('id')->middleware('permission:users.update');

        // Reports
        Route::get('reports/summary', [ReportController::class, 'summary'])->middleware('permission:reports.view');

        // Inspections — queue / status / report
        Route::get('inspection-categories', [AdminInspectionController::class, 'categories'])->middleware('permission:inspections.view');
        Route::get('inspection-requests', [AdminInspectionController::class, 'index'])->middleware('permission:inspections.view');
        Route::get('inspection-requests/{id}', [AdminInspectionController::class, 'show'])->whereNumber('id')->middleware('permission:inspections.view');
        Route::post('inspection-requests/{id}/assign', [AdminInspectionController::class, 'assign'])->whereNumber('id')->middleware('permission:inspections.update');
        Route::post('inspection-requests/{id}/status', [AdminInspectionController::class, 'updateStatus'])->whereNumber('id')->middleware('permission:inspections.update');
        Route::post('inspection-requests/{id}/report', [AdminInspectionController::class, 'saveReport'])->whereNumber('id')->middleware('permission:inspections.update');

        // Service providers — verification
        Route::get('service-providers', [AdminServiceProviderController::class, 'index'])->middleware('permission:providers.view');
        Route::post('service-providers/{id}/status', [AdminServiceProviderController::class, 'setStatus'])->whereNumber('id')->middleware('permission:providers.update');

        // Marketplace car listings — review managed sales, price, feature
        Route::get('car-listings', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'index'])->middleware('permission:listings.view');
        Route::get('car-listings/{id}', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'show'])->whereNumber('id')->middleware('permission:listings.view');
        Route::post('car-listings/{id}/status', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setStatus'])->whereNumber('id')->middleware('permission:listings.update');
        Route::post('car-listings/{id}/price', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setPrice'])->whereNumber('id')->middleware('permission:listings.update');
        Route::post('car-listings/{id}/featured', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setFeatured'])->whereNumber('id')->middleware('permission:listings.update');

        // Service categories CRUD
        Route::get('service-categories', [ServiceCategoryController::class, 'index'])->middleware('permission:categories.view');
        Route::post('service-categories', [ServiceCategoryController::class, 'store'])->middleware('permission:categories.create');
        Route::put('service-categories/{id}', [ServiceCategoryController::class, 'update'])->whereNumber('id')->middleware('permission:categories.update');
        Route::delete('service-categories/{id}', [ServiceCategoryController::class, 'destroy'])->whereNumber('id')->middleware('permission:categories.delete');

        // Staff (admin users)
        Route::get('staff', [StaffController::class, 'index'])->middleware('permission:staff.view');
        Route::post('staff', [StaffController::class, 'store'])->middleware('permission:staff.create');
        Route::put('staff/{id}', [StaffController::class, 'update'])->whereNumber('id')->middleware('permission:staff.update');
        Route::delete('staff/{id}', [StaffController::class, 'destroy'])->whereNumber('id')->middleware('permission:staff.delete');
    });
});
