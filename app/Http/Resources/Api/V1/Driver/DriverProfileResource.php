<?php


namespace App\Http\Resources\Api\V1\Driver;

use App\Http\Resources\Api\V1\ApiResource;

class DriverProfileResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'cnic_number'         => $this->cnic_number,
            'cnic_front_image'    => $this->cnic_front_image,
            'cnic_back_image'     => $this->cnic_back_image,
            'license_number'      => $this->license_number,
            'license_front_image' => $this->license_front_image,
            'license_back_image'  => $this->license_back_image,
            'verification_status' => $this->verification_status,
            'is_online'           => $this->is_online,
            'rating_avg'          => $this->rating_avg,
            'total_trips'         => $this->total_trips,
            'total_earnings'      => $this->total_earnings,
        ];
    }
}
