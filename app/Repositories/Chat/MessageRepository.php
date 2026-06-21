<?php

namespace App\Repositories\Chat;

use App\Models\Message;
use App\Repositories\BaseRepository;

class MessageRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Message();
    }

    /** Thread messages, newest first (the app reverses for display). */
    public function paginatedForConversation(int $conversationId, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('conversation_id', $conversationId)->orderByDesc('id'),
            limit: $limit,
        );
    }

    /** Mark the other party's unread messages as read; returns affected count. */
    public function markReadForViewer(int $conversationId, int $viewerId): int
    {
        return $this->model->newQuery()
            ->where('conversation_id', $conversationId)
            ->where('sender_id', '!=', $viewerId)
            ->whereNull('read_at')
            ->update(['read_at' => now()]);
    }
}
