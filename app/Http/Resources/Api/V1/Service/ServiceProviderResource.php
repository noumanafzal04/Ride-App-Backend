<?php

namespace App\Http\Resources\Api\V1\Service;

use App\Http\Resources\Api\V1\ApiResource;

class ServiceProviderResource extends ApiResource
{
    public function toArray($request): array
    {
        return [
            'id'            => $this->id,
            'user_id'       => $this->user_id,
            // True when the authenticated app user owns this listing — so the
            // app can show "Your listing" and disable booking your own service.
            'is_mine'       => $request->user() ? (int) $request->user()->id === (int) $this->user_id : false,
            'business_name' => $this->business_name,
            'status'        => $this->status,
            'city'          => $this->whenLoaded('city', fn() => [
                'id'   => $this->city?->id,
                'name' => $this->city?->name,
            ]),
            'area'          => $this->area,
            'phone'         => $this->phone,
            'description'   => $this->description,
            'rating_avg'    => $this->rating_avg,
            'total_jobs'    => $this->total_jobs,
            'categories'    => $this->whenLoaded('categories', fn() => $this->categories->map(fn($c) => [
                'id'   => $c->id,
                'name' => $c->name,
                'slug' => $c->slug,
                'icon' => $c->icon,
            ])),
            'created_at'    => $this->created_at?->toISOString(),
        ];
    }
}
