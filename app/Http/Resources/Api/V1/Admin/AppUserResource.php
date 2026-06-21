<?php

namespace App\Http\Resources\Api\V1\Admin;

use Illuminate\Http\Resources\Json\JsonResource;

class AppUserResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id'           => $this->id,
            'name'         => trim($this->first_name . ' ' . $this->last_name),
            'email'        => $this->email,
            'phone'        => $this->phone_number,
            'user_type'    => $this->user_type?->value,
            'status'       => $this->status?->value,
            'created_at'   => $this->created_at?->toISOString(),
            'driver_profile' => $this->whenLoaded('driverProfile', fn() => $this->driverProfile ? [
                'verification_status' => $this->driverProfile->verification_status,
                'rating_avg'          => $this->driverProfile->rating_avg,
                'total_trips'         => $this->driverProfile->total_trips,
                'cnic_number'         => $this->driverProfile->cnic_number,
                'license_number'      => $this->driverProfile->license_number,
            ] : null),
        ];
    }
}
