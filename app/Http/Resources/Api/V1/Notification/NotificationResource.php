<?php

namespace App\Http\Resources\Api\V1\Notification;

use App\Http\Resources\Api\V1\ApiResource;

class NotificationResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'title'      => $this->title,
            'message'    => $this->message,
            'data'       => $this->data,
            'is_read'    => !is_null($this->read_at),
            'read_at'    => $this->read_at?->toISOString(),
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
