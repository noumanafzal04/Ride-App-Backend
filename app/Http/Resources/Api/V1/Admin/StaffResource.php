<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class StaffResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'name'          => $this->name,
            'email'         => $this->email,
            'status'        => $this->status,
            'role'          => $this->whenLoaded('role', fn() => $this->role ? [
                'id'   => $this->role->id,
                'name' => $this->role->name,
                'slug' => $this->role->slug,
            ] : null),
            'last_login_at' => $this->last_login_at?->toISOString(),
            'created_at'    => $this->created_at?->toISOString(),
        ]
        ;
    }
}
