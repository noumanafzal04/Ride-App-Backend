<?php

namespace App\Http\Resources\Api\V1\Auth;

use App\Http\Resources\Api\V1\ApiResource;
use Illuminate\Http\Request;

class UserResource extends ApiResource
{
    public function toArray(
        Request $request
    ): array {

        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'email_verified_at' => $this->email_verified_at,
            'created_at' => $this->created_at,
        ];
    }
}
