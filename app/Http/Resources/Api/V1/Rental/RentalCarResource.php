<?php

namespace App\Http\Resources\Api\V1\Rental;

use App\Http\Resources\Api\V1\ApiResource;
use App\Models\RentalCar;

class RentalCarResource extends ApiResource
{
    public function toArray($request): array
    {
        $user = $request->user();
        $insp = $this->whenLoaded('inspectionRequest') ? $this->inspectionRequest : null;
        $isInspected = $insp && $insp->status === 'completed' && $insp->overall_grade;

        return [
            'id'                 => $this->id,
            'listing_type'       => $this->listing_type,
            'is_managed'         => $this->listing_type === RentalCar::TYPE_MANAGED,
            'status'             => $this->status,

            'title'              => trim("{$this->make} {$this->model}") . ($this->year ? " {$this->year}" : ''),
            'make'               => $this->make,
            'model'              => $this->model,
            'variant'            => $this->variant,
            'year'               => $this->year,
            'category'           => $this->category,
            'seats'              => $this->seats,
            'transmission'       => $this->transmission,
            'fuel_type'          => $this->fuel_type,
            'color'              => $this->color,

            'rental_type'        => $this->rental_type,
            'price_per_day'      => $this->price_per_day !== null ? (float) $this->price_per_day : null,
            'price_per_day_self' => $this->price_per_day_self !== null ? (float) $this->price_per_day_self : null,
            'deposit'            => $this->deposit !== null ? (float) $this->deposit : null,
            'min_days'           => $this->min_days,

            'city'               => $this->whenLoaded('city', fn() => ['id' => $this->city?->id, 'name' => $this->city?->name]),
            'area'               => $this->area,
            'description'        => $this->description,
            'features'           => $this->features ?? [],

            'images'             => $this->whenLoaded('images', fn() => $this->images->map(fn($i) => [
                'id' => $i->id, 'path' => $i->path, 'is_primary' => (bool) $i->is_primary,
            ])->values()),
            'primary_image'      => $this->whenLoaded('images', function () {
                $img = $this->images->firstWhere('is_primary', true) ?? $this->images->first();
                return $img?->path;
            }),

            'is_inspected'       => (bool) $isInspected,
            'inspection'         => $isInspected ? ['id' => $insp->id, 'grade' => $insp->overall_grade, 'score' => $insp->overall_score] : null,

            'is_featured'        => (bool) $this->is_featured,
            'is_mine'            => $user ? (int) $user->id === (int) $this->user_id : false,
            'owner'              => $this->whenLoaded('owner', fn() => [
                'id'    => $this->owner?->id,
                'name'  => trim("{$this->owner?->first_name} {$this->owner?->last_name}") ?: 'Owner',
                'phone' => $this->owner?->phone_number,
            ]),

            'views_count'        => $this->views_count,
            'created_at'         => $this->created_at?->toISOString(),
        ];
    }
}
