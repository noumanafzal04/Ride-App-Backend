<?php

namespace App\Http\Resources\Api\V1\Billing;

use App\Http\Resources\Api\V1\ApiResource;

class SubscriptionResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'module'        => $this->module,
            'plan'          => $this->whenLoaded('plan', fn() => [
                'id'   => $this->plan?->id,
                'name' => $this->plan?->name,
            ]),
            'user'          => $this->whenLoaded('user', fn() => $this->user ? [
                'id'   => $this->user->id,
                'name' => trim("{$this->user->first_name} {$this->user->last_name}") ?: 'User',
                'phone' => $this->user->phone_number,
            ] : null),
            'posts_allowed' => $this->posts_allowed,
            'posts_used'    => $this->posts_used,
            'posts_left'    => $this->postsLeft(),
            'starts_at'     => $this->starts_at?->toISOString(),
            'ends_at'       => $this->ends_at?->toISOString(),
            'status'        => $this->status,
            'source'        => $this->source,
            'price_paid'    => (float) $this->price_paid,
        ];
    }
}
