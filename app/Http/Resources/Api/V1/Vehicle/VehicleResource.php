<?php


namespace App\Http\Resources\Api\V1\Vehicle;

use App\Http\Resources\Api\V1\ApiResource;

class VehicleResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'                  => $this->id,
            'model_id'            => $this->model_id,
            'vehicle_image_path'  => $this->vehicle_image_path,
            'manufacture_year'    => $this->manufacture_year,
            'color'               => $this->color,
            'registration_number' => $this->registration_number,
            'seating_capacity'    => $this->seating_capacity,
            'luggage_capacity'    => $this->luggage_capacity,
            'has_air_conditioner' => $this->has_air_conditioner,
            'status'              => $this->status,
        ];
    }
}
