<?php

namespace App\Repositories\Admin;

use App\Models\User;
use App\Repositories\BaseRepository;

class AppUserRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new User();
    }

    /** Headline counts for the admin Users dashboard cards. */
    public function adminStats(): array
    {
        return [
            'total'           => (int) $this->model->newQuery()->count(),
            'drivers'         => (int) $this->model->newQuery()->where('user_type', 'driver')->count(),
            'riders'          => (int) $this->model->newQuery()->where('user_type', 'user')->count(),
            'pending_drivers' => (int) $this->model->newQuery()
                ->whereHas('driverProfile', fn($d) => $d->where('verification_status', 'pending'))->count(),
        ];
    }

    /** App users (riders/drivers) for the admin list. Optional type + verification filters. */
    public function paginatedForAdmin(array $filters = [], ?int $limit = null)
    {
        return $this->paginatedList(
            callback: function ($q) use ($filters) {
                if (!empty($filters['user_type'])) {
                    $q->where('user_type', $filters['user_type']);
                }
                if (!empty($filters['verification'])) {
                    $q->whereHas('driverProfile', fn($d) => $d->where('verification_status', $filters['verification']));
                }
                if (!empty($filters['search'])) {
                    $s = $filters['search'];
                    $q->where(fn($w) => $w->where('first_name', 'like', "%$s%")
                        ->orWhere('last_name', 'like', "%$s%")
                        ->orWhere('email', 'like', "%$s%")
                        ->orWhere('phone_number', 'like', "%$s%"));
                }
                $q->latest();
            },
            relations: ['driverProfile:id,user_id,verification_status,rating_avg,total_trips'],
            limit: $limit,
        );
    }

    public function findDetail(int $id): User
    {
        return $this->model->newQuery()
            ->with(['profile', 'driverProfile'])
            ->findOrFail($id);
    }
}
