<?php

namespace App\Actions\Notification;

use App\Actions\BaseAction\BaseAction;
use App\Repositories\Notification\NotificationRepository;

class NotificationAction extends BaseAction
{
    public function __construct(NotificationRepository $repository)
    {
        parent::__construct($repository, 'notification');
    }

    public function listForUser(int $userId)
    {
        return $this->repository->paginatedList(
            callback: fn($q) => $q->where('user_id', $userId)->latest(),
        );
    }

    public function unreadCount(int $userId): int
    {
        return $this->repository->unreadCountForUser($userId);
    }

    public function markRead(int $userId, int $id): void
    {
        $this->repository->markReadForUser($userId, $id);
    }

    public function markAllRead(int $userId): void
    {
        $this->repository->markAllReadForUser($userId);
    }
}
