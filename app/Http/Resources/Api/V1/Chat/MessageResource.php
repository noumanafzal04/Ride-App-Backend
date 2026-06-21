<?php

namespace App\Http\Resources\Api\V1\Chat;

use App\Http\Resources\Api\V1\ApiResource;

class MessageResource extends ApiResource
{
    public function toArray($request): array
    {
        $viewerId = (int) ($request->user()?->id);

        return [
            'id'              => $this->id,
            'conversation_id' => $this->conversation_id,
            'sender_id'       => $this->sender_id,
            'is_mine'         => $this->sender_id === $viewerId,
            'body'            => $this->body,
            'read_at'         => $this->read_at?->toISOString(),
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
