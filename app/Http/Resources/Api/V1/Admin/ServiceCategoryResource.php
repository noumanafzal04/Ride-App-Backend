<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class ServiceCategoryResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'              => $this->id,
            'name'            => $this->name,
            'slug'            => $this->slug,
            'icon'            => $this->icon,
            'sort'            => $this->sort,
            'is_active'       => (bool) $this->is_active,
            'providers_count' => $this->whenCounted('providers'),
        ];
    }
}
