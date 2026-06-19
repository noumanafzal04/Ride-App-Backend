<?php

namespace App\Http\Resources\Api\V1\Ride;

use App\Http\Resources\Api\V1\ApiResource;

class RatingResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'         => $this->id,
            'type'       => $this->type,
            'rated_as'   => $this->rated_as,
            'rating'     => $this->rating,
            'review'     => $this->review,
            'created_at' => $this->created_at?->toISOString(),
        ];
    }
}
