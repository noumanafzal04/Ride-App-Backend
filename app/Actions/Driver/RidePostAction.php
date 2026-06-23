<?php
// app/Actions/Driver/RidePostAction.php

namespace App\Actions\Driver;

use App\Actions\BaseAction\BaseAction;
use App\Constants\ResourceFields;
use App\Exceptions\ApiException;
use App\Events\RidePostCreated;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\BookingRepository;
use App\Repositories\Ride\RideAlertRepository;
use App\Services\Notification\NotificationService;
use App\Support\BuildsWithRelations;
use App\Models\RidePost;
use Illuminate\Support\Facades\DB;
use Throwable;

class RidePostAction extends BaseAction
{
    protected ?array $rideGate = null;

    public function __construct(
        RidePostRepository $repository,
        protected RideAlertRepository $alerts,
        protected NotificationService $notifications,
        protected BookingRepository $bookings,
        protected DriverProfileRepository $driverProfiles,
        protected \App\Services\Chat\ChatService $chat,
        protected \App\Services\Billing\BillingService $billing,
    ) {
        parent::__construct($repository, 'ride_post');
    }

    /**
     * After a ride is posted, notify riders whose "notify me" alert matches the
     * route (and date). Indexed lookup; for very hot routes this would move to a
     * queued job, but it's fine synchronously at current scale.
     */
    protected function afterCreate($created, int $companyId, $data): void
    {
        // Consume one post against the active plan (no-op when the post was free).
        $this->billing->consume($created->driver_id, \App\Constants\BillingModule::RIDE, $this->rideGate);

        $created->load(['fromCity:id,name', 'toCity:id,name']);
        $date = $created->departure_at ? $created->departure_at->toDateString() : null;

        // Live: push the new post to everyone browsing (they filter client-side).
        try {
            broadcast(new RidePostCreated($created));
        } catch (Throwable $e) {
            report($e);
        }

        $matches = $this->alerts->matchingForPost(
            $created->from_city_id,
            $created->to_city_id,
            $date,
            $created->driver_id,
        );

        $from = $created->fromCity?->name ?? 'your city';
        $to   = $created->toCity?->name ?? 'the destination';

        foreach ($matches as $alert) {
            $this->notifications->push(
                $alert->user_id,
                'ride_alert',
                'Ride available',
                "A ride from {$from} to {$to} was just posted.",
                ['ride_post_id' => $created->id],
            );
        }
    }

    // ── hooks ──────────────────────────────────────────────

    protected function beforeCreate(int $driverId, $data): array
    {
        // Only verified drivers may post rides.
        $profile = $this->driverProfiles->forUser($driverId);
        if (!$profile || $profile->verification_status !== 'verified') {
            throw new ApiException('Your account is pending verification. You can post rides once verified.', 403);
        }

        // Subscription gate (free while billing is disabled for rides).
        $this->rideGate = $this->billing->assertCanPost($driverId, \App\Constants\BillingModule::RIDE);

        $data['driver_id'] = $driverId;
        $data['status']    = 'active';

        // Driver chooses available seats for shared (validated ≤ capacity − 1); private = whole vehicle.
        $data['available_seats'] = $data['post_type'] === 'shared'
            ? (int) ($data['available_seats'] ?? 1)
            : null;

        return $data;
    }
    protected function beforeUpdate(int $driverId, $id, $data): array
    {
        // ownership guard
        $post = $this->repository->findOrFail($id);

        if ($post->driver_id !== $driverId) {
            throw new ApiException('You do not own this ride post.', 403);
        }

        // private posts never track seats
        if (isset($data['post_type']) && $data['post_type'] === 'private') {
            $data['available_seats'] = null;
        }

        return $data;
    }
    /**
     * Cancelling a posted ride is a SOFT cancel (not a hard delete): cancel any
     * pending/accepted bookings and notify those riders, then mark the post
     * cancelled. Preserves history and never silently drops a confirmed rider.
     */
    public function destroy($companyId, $id, $options = [])
    {
        return DB::transaction(function () use ($companyId, $id) {
            $post = $this->repository->findOrFail($id);

            if ($post->driver_id !== $companyId) {
                throw new ApiException('You do not own this ride post.', 403);
            }
            if ($post->status === 'in_progress') {
                throw new ApiException('A ride in progress can’t be cancelled — end it instead.', 422);
            }
            if (in_array($post->status, ['completed', 'cancelled'])) {
                throw new ApiException('This ride is already closed.', 422);
            }

            // Cancel + notify active riders
            $active = $this->bookings->list(
                callback: fn($q) => $q
                    ->where('ride_post_id', $id)
                    ->whereIn('status', ['pending', 'accepted'])
            );
            foreach ($active as $b) {
                $this->bookings->update($b->id, ['status' => 'cancelled']);
                $this->notifications->push(
                    $b->passenger_id,
                    'ride_cancelled',
                    'Ride cancelled',
                    'The driver cancelled the ride you booked.',
                    ['ride_post_id' => $id, 'booking_id' => $b->id],
                );
            }

            $this->repository->update($id, ['status' => 'cancelled']);

            // Ride pulled → close any open conversations on it.
            $this->chat->closeForRidePost($id);

            return true;
        });
    }
    // ── overrides using base repo methods ──────────────────

    public function all(int $driverId, ?array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($driverId) {
                $query->where('driver_id', $driverId);
            },
            select: ResourceFields::RIDE_POST_LIST_FIELDS,
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                ['fromCity', 'toCity']
            ),
        );
    }

    /**
     * Count browseable posts newer than `after_id` for the rider's current
     * filters. Powers the app's "new rides available" banner via a cheap COUNT
     * query, so the full list is only re-fetched when the rider taps it.
     */
    public function newCount(int $userId, ?array $filters = []): int
    {
        $afterId = (int) ($filters['after_id'] ?? 0);

        // Nothing loaded yet → the banner is irrelevant (the list itself loads).
        if ($afterId <= 0) {
            return 0;
        }

        return $this->repository->newerBrowseableCount($userId, $afterId, $filters);
    }

    /**
     * Rider-facing browse: active, upcoming posts (excluding the viewer's own),
     * with driver + vehicle + cities. Optional filters: from_city_id, to_city_id, date.
     */
    public function browse(int $userId, ?array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($userId, $filters) {
                $query->where('driver_id', '!=', $userId)
                    ->where('status', 'active')
                    ->where('departure_at', '>', now())
                    ->where(function ($q) {
                        $q->whereNull('available_seats')        // private — whole vehicle
                            ->orWhere('available_seats', '>', 0); // shared — seats left
                    })
                    // hide rides the rider already has an active booking on
                    ->whereDoesntHave('bookings', function ($q) use ($userId) {
                        $q->where('passenger_id', $userId)
                            ->whereIn('status', ['pending', 'accepted']);
                    });

                if (!empty($filters['from_city_id'])) {
                    $query->where('from_city_id', $filters['from_city_id']);
                }
                if (!empty($filters['to_city_id'])) {
                    $query->where('to_city_id', $filters['to_city_id']);
                }
                if (!empty($filters['date'])) {
                    $query->whereDate('departure_at', $filters['date']);
                }

                // Location-aware ranking: show ALL rides, but surface those
                // departing nearest the user first. Only when no city filter is
                // applied (an explicit from/to keeps the soonest-departure order).
                $nearLat = $filters['near_lat'] ?? null;
                $nearLng = $filters['near_lng'] ?? null;
                $hasCityFilter = !empty($filters['from_city_id']) || !empty($filters['to_city_id']);

                if ($nearLat !== null && $nearLng !== null && !$hasCityFilter) {
                    $query->select('ride_posts.*')
                        ->selectRaw(
                            '( 6371 * acos( least(1, greatest(-1,'
                            . ' cos(radians(?)) * cos(radians(from_latitude)) * cos(radians(from_longitude) - radians(?))'
                            . ' + sin(radians(?)) * sin(radians(from_latitude)) ))) ) AS distance_km',
                            [(float) $nearLat, (float) $nearLng, (float) $nearLat]
                        )
                        ->orderByRaw('distance_km IS NULL')  // rides without coords go last
                        ->orderBy('distance_km')
                        ->orderBy('departure_at');
                } else {
                    $query->orderBy('departure_at');
                }
            },
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }

    public function show($driverId, $id)
    {
        return $this->repository->findOne(
            callback: function ($query) use ($driverId, $id) {
                $query->where('id', $id)
                    ->where('driver_id', $driverId);
            },
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }

    /**
     * Rider-facing detail of a single ride post (any driver), with driver + vehicle + cities.
     */
    public function showForRider($id)
    {
        return $this->repository->findOrFail(
            $id,
            relations: BuildsWithRelations::relations(
                RidePost::RESOURCE_RELATIONS,
                [
                    'driver',
                    'driver.vehicles',
                    'driver.vehicles.vehicleModel',
                    'driver.vehicles.vehicleModel.make',
                    'fromCity',
                    'toCity',
                ]
            ),
        );
    }
}
