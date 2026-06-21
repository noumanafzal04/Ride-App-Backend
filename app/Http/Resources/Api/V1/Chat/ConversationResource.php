<?php

namespace App\Http\Resources\Api\V1\Chat;

use App\Http\Resources\Api\V1\ApiResource;

class ConversationResource extends ApiResource
{
    public function toArray($request): array
    {
        $viewerId = (int) ($request->user()?->id);
        $isDriver = $viewerId === $this->driver_id;
        $other    = $isDriver ? $this->rider : $this->driver;
        $unread   = $isDriver ? $this->driver_unread : $this->rider_unread;

        $route = null;
        if ($this->relationLoaded('ridePost') && $this->ridePost) {
            $route = trim(($this->ridePost->fromCity?->name ?? '') . ' → ' . ($this->ridePost->toCity?->name ?? ''), ' →');
        }

        // Service context (for service-type conversations).
        $service = null;
        if ($this->relationLoaded('serviceBooking') && $this->serviceBooking) {
            $sb = $this->serviceBooking;
            $service = [
                'service_booking_id' => $sb->id,
                'category'           => $sb->category?->name,
                'icon'               => $sb->category?->icon,
                'business_name'      => $sb->provider?->business_name,
            ];
            if (!$route) {
                $route = $sb->category?->name; // inbox subtitle for service chats
            }
        }

        // Compact ride summary for the chat banner (route / seats / fare → tap opens RideDetail).
        $ride = null;
        if ($this->relationLoaded('ridePost') && $this->ridePost) {
            $ride = [
                'ride_post_id'   => $this->ride_post_id,
                'route'          => $route ?: null,
                'departure_at'   => $this->ridePost->departure_at?->toISOString(),
                'price_per_seat' => $this->ridePost->price_per_seat,
                'seats'          => $this->relationLoaded('booking') ? $this->booking?->seats_booked : null,
                'fare'           => $this->relationLoaded('booking') ? $this->booking?->total_amount : null,
            ];
        }

        return [
            'id'              => $this->id,
            'type'            => $this->type,
            'booking_id'      => $this->booking_id,
            'ride_post_id'    => $this->ride_post_id,
            'service'         => $service,
            'status'          => $this->status,
            'other_party'     => $other ? [
                'id'   => $other->id,
                // In a service chat the customer should see the business name.
                'name' => ($this->type === 'service' && !$isDriver && !empty($service['business_name']))
                    ? $service['business_name']
                    : trim($other->first_name . ' ' . $other->last_name),
            ] : null,
            'route'           => $route ?: null,
            'ride'            => $ride,
            'last_message'    => $this->last_message_preview,
            'last_message_at' => $this->last_message_at?->toISOString(),
            'unread_count'    => (int) $unread,
            'created_at'      => $this->created_at?->toISOString(),
        ];
    }
}
