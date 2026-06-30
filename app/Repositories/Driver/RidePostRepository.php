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

    /** Headline counts for the admin Rides dashboard cards. */
    public function adminStats(): array
    {
        return [
            'total'     => (int) $this->model->newQuery()->count(),
            'active'    => (int) $this->model->newQuery()->where('status', 'active')->count(),
            'completed' => (int) $this->model->newQuery()->where('status', 'completed')->count(),
            'cancelled' => (int) $this->model->newQuery()->where('status', 'cancelled')->count(),
        ];
    }

    /** Admin ride listing — driver + cities + booking count, optional filters. */
    public function paginatedForAdmin(array $filters = [], ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters) {
                if (!empty($filters['status']))  $q->where('status', $filters['status']);
                if (!empty($filters['city_id'])) {
                    $q->where(fn($w) => $w->where('from_city_id', $filters['city_id'])->orWhere('to_city_id', $filters['city_id']));
                }
                if (!empty($filters['search'])) {
                    $s = $filters['search'];
                    $q->whereHas('driver', fn($d) => $d
                        ->where('first_name', 'like', "%{$s}%")
                        ->orWhere('last_name', 'like', "%{$s}%")
                        ->orWhere('phone_number', 'like', "%{$s}%"));
                }
                $q->withCount('bookings')->latest('departure_at');
            },
            relations: [
                'driver:id,first_name,last_name,phone_number',
                'fromCity:id,name',
                'toCity:id,name',
            ],
            limit: $limit,
        );
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
     * A driver's completed rides, paginated — powers the "Recent Trips" tab.
     */
    public function paginatedCompletedForDriver(int $driverId)
    {
        return $this->paginatedList(
            callback: fn($q) => $q
                ->where('driver_id', $driverId)
                ->where('status', 'completed')
                ->latest('departure_at'),
            relations: ['fromCity:id,name', 'toCity:id,name'],
        );
    }

    /**
     * Rides still open (active/full/in_progress) past their departure time plus
     * a grace period — used by the scheduled auto-close so a driver who forgets
     * to end a ride is never left stuck (one-active-post rule).
     */
    public function staleOpenRides(int $graceHours)
    {
        // Only rides that actually carried passengers get auto-completed after the
        // grace window. Empty ones are cancelled immediately (emptyExpiredRides).
        return $this->model->newQuery()
            ->whereIn('status', ['active', 'full', 'in_progress'])
            ->where('departure_at', '<', now()->subHours($graceHours))
            ->whereHas('bookings', fn($q) => $q->where('status', 'accepted'))
            ->get();
    }

    /**
     * Posts whose departure has passed with NO accepted passenger — these are
     * dead and should be cancelled the moment departure arrives (no grace).
     * Scoped to one driver when $driverId is given (cheap on-app-open cleanup).
     */
    public function emptyExpiredRides(?int $driverId = null)
    {
        return $this->model->newQuery()
            ->whereIn('status', ['active', 'full'])
            ->where('departure_at', '<', now())
            ->when($driverId, fn($q) => $q->where('driver_id', $driverId))
            ->whereDoesntHave('bookings', fn($q) => $q->where('status', 'accepted'))
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
