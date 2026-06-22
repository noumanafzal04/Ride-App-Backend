<?php

namespace App\Http\Resources\Api\V1\Marketplace;

use App\Http\Resources\Api\V1\ApiResource;
use App\Models\CarListing;

class CarListingResource extends ApiResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $insp = $this->whenLoaded('inspectionRequest') ? $this->inspectionRequest : null;
        $isInspected = $insp && $insp->status === 'completed' && $insp->overall_grade;

        $images = $this->whenLoaded('images', fn() => $this->images->map(fn($i) => [
            'id'         => $i->id,
            'path'       => $i->path,           // relative — app builds the URL from its host
            'is_primary' => (bool) $i->is_primary,
        ])->values());

        $primary = $this->whenLoaded('images', function () {
            $img = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
            return $img?->path;
        });

        return [
            'id'           => $this->id,
            'listing_type' => $this->listing_type,
            'is_managed'   => $this->listing_type === CarListing::TYPE_MANAGED,
            'status'       => $this->status,

            'title'        => trim("{$this->make} {$this->model}") . ($this->year ? " {$this->year}" : ''),
            'make'         => $this->make,
            'model'        => $this->model,
            'variant'      => $this->variant,
            'year'         => $this->year,
            'price'        => $this->price !== null ? (float) $this->price : null,
            'mileage'      => $this->mileage,
            'condition'    => $this->condition,
            'transmission' => $this->transmission,
            'fuel_type'    => $this->fuel_type,
            'engine_cc'    => $this->engine_cc,
            'color'        => $this->color,

            'city'         => $this->whenLoaded('city', fn() => [
                'id'   => $this->city?->id,
                'name' => $this->city?->name,
            ]),
            'area'         => $this->area,
            'description'  => $this->description,
            'features'     => $this->features ?? [],

            'images'        => $images,
            'primary_image' => $primary,

            'is_inspected' => (bool) $isInspected,
            'inspection'   => $isInspected ? [
                'id'    => $insp->id,
                'grade' => $insp->overall_grade,
                'score' => $insp->overall_score,
            ] : null,

            'is_featured'  => (bool) $this->is_featured,
            'is_mine'      => $user ? (int) $user->id === (int) $this->user_id : false,

            'seller'       => $this->whenLoaded('seller', fn() => [
                'id'    => $this->seller?->id,
                'name'  => trim("{$this->seller?->first_name} {$this->seller?->last_name}") ?: 'Seller',
                'phone' => $this->seller?->phone_number,
            ]),

            'views_count'  => $this->views_count,
            'created_at'   => $this->created_at?->toISOString(),
            'sold_at'      => $this->sold_at?->toISOString(),
        ];
    }
}
