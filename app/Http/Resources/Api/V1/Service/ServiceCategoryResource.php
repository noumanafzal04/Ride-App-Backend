<?php

namespace App\Http\Resources\Api\V1\Service;

use App\Http\Resources\Api\V1\ApiResource;

class ServiceCategoryResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'icon'            => $this->icon,
            'providers_count' => (int) ($this->providers_count ?? 0),
        ];
    }
}
