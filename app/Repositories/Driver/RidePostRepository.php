<?php

namespace App\Repositories\Driver;

use App\Models\RidePost;
use App\Repositories\BaseRepository;
use App\Constants\ResourceFields;
use App\Support\BuildsWithRelations;

class RidePostRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RidePost();
    }

    public function findActiveForBooking(int $id): RidePost
    {
        return $this->model
            ->lockForUpdate()
            ->findOrFail($id);
    }

    /**
     * Count browseable posts newer than $afterId for the given rider + filters.
     * Mirrors the constraints in RidePostAction::browse() — a single cheap COUNT.
     */
    public function newerBrowseableCount(int $userId, int $afterId, array $filters = []): int
    {
        $query = $this->model->newQuery()
            ->where('id', '>', $afterId)
            ->where('driver_id', '!=', $userId)
            ->where('status', 'active')
            ->where('departure_at', '>', now())
            ->where(function ($q) {
                $q->whereNull('available_seats')        // private — whole vehicle
                    ->orWhere('available_seats', '>', 0); // shared — seats left
            })
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

        return $query->count();
    }

    /**
     * Rides still open (active/full/in_progress) past their departure time plus
     * a grace period — used by the scheduled auto-close so a driver who forgets
     * to end a ride is never left stuck (one-active-post rule).
     */
    public function staleOpenRides(int $graceHours)
    {
        return $this->model->newQuery()
            ->whereIn('status', ['active', 'full', 'in_progress'])
            ->where('departure_at', '<', now()->subHours($graceHours))
            ->get();
    }

    protected function applyFilters($query, array $filters)
    {
        if (!empty($filters['status'])) {
            $query->where('status', $filters['status']);
        }

        if (!empty($filters['post_type'])) {
            $query->where('post_type', $filters['post_type']);
        }

        return $query;
    }
}
