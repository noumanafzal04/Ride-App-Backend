<?php

use App\Http\Controllers\Api\V1\Notification\NotificationController;
use Illuminate\Support\Facades\Route;

// Static routes first so they aren't captured by {id}
Route::get('notifications', [NotificationController::class, 'index']);
Route::get('notifications/unread-count', [NotificationController::class, 'unreadCount']);
Route::post('notifications/read-all', [NotificationController::class, 'markAllRead']);
Route::post('notifications/{id}/read', [NotificationController::class, 'markRead']);
