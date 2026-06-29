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
        Route::post('notifications/broadcast', [NotificationController::class, 'broadcast'])->middleware('permission:settings.update');

        // City list (for targeted broadcasts / filters)
        Route::get('cities', [\App\Http\Controllers\Api\V1\WorldController::class, 'cities']);

        // Roles & permissions
        Route::get('permissions', [RoleController::class, 'permissions'])->middleware('permission:roles.view');
        Route::get('roles', [RoleController::class, 'index'])->middleware('permission:roles.view');
        Route::get('roles/{id}', [RoleController::class, 'show'])->whereNumber('id')->middleware('permission:roles.view');
        Route::post('roles', [RoleController::class, 'store'])->middleware('permission:roles.create');
        Route::put('roles/{id}', [RoleController::class, 'update'])->whereNumber('id')->middleware('permission:roles.update');
        Route::delete('roles/{id}', [RoleController::class, 'destroy'])->whereNumber('id')->middleware('permission:roles.delete');

        // App users + profile verification
        Route::post('app-users', [UserController::class, 'store'])->middleware('permission:users.update');
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
        Route::post('service-providers', [AdminServiceProviderController::class, 'store'])->middleware('permission:providers.update');
        Route::get('service-providers', [AdminServiceProviderController::class, 'index'])->middleware('permission:providers.view');
        Route::post('service-providers/{id}/status', [AdminServiceProviderController::class, 'setStatus'])->whereNumber('id')->middleware('permission:providers.update');

        // Marketplace car listings — review managed sales, price, feature
        Route::get('car-listings', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'index'])->middleware('permission:listings.view');
        Route::get('car-listings/{id}', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'show'])->whereNumber('id')->middleware('permission:listings.view');
        Route::post('car-listings/{id}/status', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setStatus'])->whereNumber('id')->middleware('permission:listings.update');
        Route::post('car-listings/{id}/price', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setPrice'])->whereNumber('id')->middleware('permission:listings.update');
        Route::post('car-listings/{id}/featured', [\App\Http\Controllers\Api\V1\Marketplace\AdminCarListingController::class, 'setFeatured'])->whereNumber('id')->middleware('permission:listings.update');

        // Rent a Car — review managed rentals, price, feature
        Route::get('rentals', [\App\Http\Controllers\Api\V1\Rental\AdminRentalController::class, 'index'])->middleware('permission:rentals.view');
        Route::get('rentals/{id}', [\App\Http\Controllers\Api\V1\Rental\AdminRentalController::class, 'show'])->whereNumber('id')->middleware('permission:rentals.view');
        Route::post('rentals/{id}/status', [\App\Http\Controllers\Api\V1\Rental\AdminRentalController::class, 'setStatus'])->whereNumber('id')->middleware('permission:rentals.update');
        Route::post('rentals/{id}/price', [\App\Http\Controllers\Api\V1\Rental\AdminRentalController::class, 'setPrice'])->whereNumber('id')->middleware('permission:rentals.update');
        Route::post('rentals/{id}/featured', [\App\Http\Controllers\Api\V1\Rental\AdminRentalController::class, 'setFeatured'])->whereNumber('id')->middleware('permission:rentals.update');

        // Billing — subscription plans, module settings, subscriptions
        Route::get('billing/plans', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'plans'])->middleware('permission:billing.view');
        Route::post('billing/plans', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'storePlan'])->middleware('permission:billing.update');
        Route::put('billing/plans/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'updatePlan'])->whereNumber('id')->middleware('permission:billing.update');
        Route::delete('billing/plans/{id}', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'destroyPlan'])->whereNumber('id')->middleware('permission:billing.update');
        Route::get('billing/settings', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'settings'])->middleware('permission:billing.view');
        Route::put('billing/settings/{module}', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'updateSetting'])->middleware('permission:billing.update');
        Route::get('billing/subscriptions', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'subscriptions'])->middleware('permission:billing.view');
        Route::post('billing/subscriptions/grant', [\App\Http\Controllers\Api\V1\Admin\AdminBillingController::class, 'grant'])->middleware('permission:billing.update');

        // Module on/off settings (which app features are live)
        Route::get('modules', [\App\Http\Controllers\Api\V1\Admin\AdminModuleController::class, 'index'])->middleware('permission:settings.view');
        Route::put('modules/{key}', [\App\Http\Controllers\Api\V1\Admin\AdminModuleController::class, 'update'])->middleware('permission:settings.update');

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
