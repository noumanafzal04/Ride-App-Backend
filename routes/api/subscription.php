<?php

use App\Http\Controllers\Api\V1\Billing\SubscriptionController;
use Illuminate\Support\Facades\Route;

// Subscriptions / membership (auth:api — required by parent group)
Route::get('subscriptions/plans', [SubscriptionController::class, 'plans']);
Route::get('subscriptions/me', [SubscriptionController::class, 'me']);
Route::post('subscriptions/subscribe', [SubscriptionController::class, 'subscribe']);
