<?php

use App\Http\Controllers\Api\V1\Chat\ChatController;
use Illuminate\Support\Facades\Route;

Route::get('conversations', [ChatController::class, 'index']);
Route::get('conversations/unread-count', [ChatController::class, 'unreadCount']);
Route::get('conversations/by-booking/{bookingId}', [ChatController::class, 'showForBooking'])->whereNumber('bookingId');
Route::get('conversations/by-service-booking/{serviceBookingId}', [ChatController::class, 'showForServiceBooking'])->whereNumber('serviceBookingId');
Route::get('conversations/by-listing/{listingId}', [ChatController::class, 'showForListing'])->whereNumber('listingId');
Route::get('conversations/{id}/messages', [ChatController::class, 'messages'])->whereNumber('id');
Route::post('conversations/{id}/messages', [ChatController::class, 'send'])->whereNumber('id');
Route::post('conversations/{id}/read', [ChatController::class, 'read'])->whereNumber('id');
