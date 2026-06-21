<?php

namespace App\Services\Notification;

use App\Events\AdminNotificationCreated;
use App\Models\AdminNotification;
use Throwable;

/**
 * Fire-and-forget admin activity feed. Never breaks the calling flow.
 */
class AdminNotificationService
{
    public function push(string $type, string $title, string $message, array $data = []): void
    {
        try {
            $notification = AdminNotification::create([
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'data'    => $data ?: null,
            ]);

            // Live push to all admins over Reverb (swallowed if Reverb is down).
            broadcast(new AdminNotificationCreated($notification));
        } catch (Throwable $e) {
            report($e);
        }
    }
}
