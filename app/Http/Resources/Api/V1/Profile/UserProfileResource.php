<?php


namespace App\Http\Resources\Api\V1\Profile;

use App\Http\Resources\Api\V1\ApiResource;

class UserProfileResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'profile_image' => $this->profile_image,
            'dob'           => $this->dob,
            'gender'        => $this->gender,
            'city'          => $this->city,
            'address'       => $this->address,
            'bio'           => $this->bio,
        ];
    }
}
