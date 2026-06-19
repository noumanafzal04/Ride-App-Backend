<?php

namespace App\Services\Notification;

use App\Repositories\Notification\NotificationRepository;
use Throwable;

class NotificationService
{
    public function __construct(protected NotificationRepository $repository) {}

    /**
     * Fire-and-forget notification write. A failure here must NEVER break the
     * calling flow (booking, accept, end-ride, …) — it is logged and swallowed.
     * The generic `type` + `data` columns let Firebase/push + deep-linking plug
     * in later without changing callers.
     */
    public function push(int $userId, string $type, string $title, string $message, array $data = []): void
    {
        try {
            $this->repository->create([
                'user_id' => $userId,
                'type'    => $type,
                'title'   => $title,
                'message' => $message,
                'data'    => $data ?: null,
            ]);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
