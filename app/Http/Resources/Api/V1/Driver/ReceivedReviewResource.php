<?php

namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;

class ReceivedReviewResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'rating'     => $this->rating,
            'review'     => $this->review,
            'from_name'  => trim(($this->fromUser?->first_name ?? '') . ' ' . ($this->fromUser?->last_name ?? '')) ?: 'Rider',
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
