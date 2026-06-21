<?php

namespace App\Http\Resources\Api\V1\Inspection;

use App\Http\Resources\Api\V1\ApiResource;

class InspectionRequestResource extends ApiResource
{
    public function toArray($request): array
    {
        // Portal admins (AdminUser) always see internal notes; app users never do.
        $user = $request->user();
        $isAdmin = $user instanceof \App\Models\AdminUser
            || (is_object($user) && method_exists($user, 'isAdmin') && $user->isAdmin());

        return [
            'id'              => $this->id,
            'tracking_token'  => $this->tracking_token,
            'status'          => $this->status,

            // Requester contact (own request or admin viewing)
            'name'            => $this->name,
            'phone'           => $this->phone,
            'email'           => $this->email,

            // Car
            'car_make'        => $this->car_make,
            'car_model'       => $this->car_model,
            'car_year'        => $this->car_year,
            'variant'         => $this->variant,
            'registration_no' => $this->registration_no,

            // Location & timing
            'city'            => $this->whenLoaded('city', fn() => [
                'id'   => $this->city?->id,
                'name' => $this->city?->name,
            ]),
            'address'         => $this->address,
            'preferred_at'    => $this->preferred_at?->toISOString(),
            'notes'           => $this->notes,

            // Workflow
            'scheduled_at'    => $this->scheduled_at?->toISOString(),
            'inspector'       => $this->whenLoaded('inspector', fn() => $this->inspector ? [
                'id'   => $this->inspector->id,
                'name' => trim($this->inspector->first_name . ' ' . $this->inspector->last_name),
            ] : null),

            // Report summary
            'overall_grade'      => $this->overall_grade,
            'overall_score'      => $this->overall_score,
            'inspector_comments' => $this->inspector_comments,
            'completed_at'       => $this->completed_at?->toISOString(),

            // Category-level report (present once the inspector fills it)
            'report' => $this->whenLoaded('categoryResults', fn() => $this->categoryResults
                ->sortBy(fn($r) => $r->category?->sort)
                ->values()
                ->map(fn($r) => [
                    'category_id' => $r->category_id,
                    'category'    => $r->category?->name,
                    'slug'        => $r->category?->slug,
                    'condition'   => $r->condition,
                    'notes'       => $r->notes,
                ])),

            // Internal — admins only
            'admin_notes'     => $this->when($isAdmin, fn() => $this->admin_notes),

            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
