<?php

namespace App\Events;

use App\Models\RidePost;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class RidePostCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(public RidePost $ridePost) {}

    public function broadcastOn(): Channel
    {
        // Public channel — anyone browsing receives new posts; the app filters
        // client-side by the route/date it currently shows.
        return new Channel('rides');
    }

    public function broadcastAs(): string
    {
        return 'ride.created';
    }

    public function broadcastWith(): array
    {
        $p = $this->ridePost;

        return [
            'id'             => $p->id,
            'from_city_id'   => $p->from_city_id,
            'to_city_id'     => $p->to_city_id,
            'date'           => $p->departure_at?->toDateString(),
            'departure_at'   => $p->departure_at?->toISOString(),
            'post_type'      => $p->post_type,
            'price_per_seat' => $p->price_per_seat,
        ];
    }
}
