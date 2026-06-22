<?php

namespace App\Services\Notification;

use App\Events\NotificationCreated;
use App\Repositories\Notification\NotificationRepository;
use Throwable;

class NotificationService
{
    public function __construct(
        protected NotificationRepository $repository,
        protected FcmService $fcm,
    ) {}

    /**
     * Fire-and-forget notification write. A failure here must NEVER break the
     * calling flow (booking, accept, end-ride, …) — it is logged and swallowed.
     * The generic `type` + `data` columns let Firebase/push + deep-linking plug
     * in later without changing callers.
     */
    public function push(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        try {
            $notification = $this->repository->create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'data'    => $data ?: null,
            ]);

            // Live push over WebSocket so the user's app updates instantly.
            // Swallowed if Reverb is unreachable — never breaks the calling flow.
            broadcast(new NotificationCreated($notification));

            // Native push (FCM) for closed/backgrounded apps. No-op until the
            // service-account JSON is present; best-effort, never throws upward.
            $this->fcm->sendToUser($userId, $type, $title, $message, $data);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
