<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class NotificationController extends Controller
{
    /** Recent admin feed + unread count (unread = created after this admin's read marker). */
    public function index(Request $request)
    {
        $admin = $request->user();
        $readAt = $admin->notifications_read_at;

        $items = AdminNotification::latest()->limit(40)->get()->map(fn($n) => [
            'id'         => $n->id,
            'type'       => $n->type,
            'title'      => $n->title,
            'message'    => $n->message,
            'data'       => $n->data,
            'is_read'    => $readAt ? $n->created_at->lte($readAt) : false,
            'created_at' => $n->created_at?->toISOString(),
        ]);

        $unread = AdminNotification::when($readAt, fn($q) => $q->where('created_at', '>', $readAt))->count();

        return ApiResponse::success(['notifications' => $items, 'unread_count' => $unread], 'Notifications.');
    }

    public function markRead(Request $request)
    {
        $request->user()->forceFill(['notifications_read_at' => now()])->save();
        return ApiResponse::success(['unread_count' => 0], 'Marked as read.');
    }

    public function unreadCount(Request $request)
    {
        $readAt = $request->user()->notifications_read_at;
        $unread = AdminNotification::when($readAt, fn($q) => $q->where('created_at', '>', $readAt))->count();
        return ApiResponse::success(['unread_count' => $unread], 'Unread count.');
    }
}
