<?php

namespace App\Http\Resources\Api\V1\Billing;

use App\Http\Resources\Api\V1\ApiResource;

class SubscriptionPlanResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'module'        => $this->module,
            'name'          => $this->name,
            'duration_days' => $this->duration_days,
            'post_limit'    => $this->post_limit,
            'price'         => (float) $this->price,
            'is_active'     => (bool) $this->is_active,
            'sort'          => $this->sort,
        ];
    }
}
