<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class PermissionResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'     => $this->id,
            'key'    => $this->key,
            'module' => $this->module,
            'action' => $this->action,
            'label'  => $this->label,
        ];
    }
}
