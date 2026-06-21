<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class RoleResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'slug'         => $this->slug,
            'description'  => $this->description,
            'is_system'    => (bool) $this->is_system,
            'permissions_count' => $this->whenCounted('permissions'),
            'staff_count'  => $this->whenCounted('adminUsers'),
            'permissions'  => $this->whenLoaded('permissions', fn() => $this->permissions->pluck('key')),
        ];
    }
}
