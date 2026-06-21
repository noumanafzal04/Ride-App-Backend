<?php

namespace App\Repositories\Inspection;

use App\Models\InspectionRequest;
use App\Repositories\BaseRepository;

class InspectionRequestRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new InspectionRequest();
    }

    /**
     * A requester's own inspection requests (newest first), with city.
     */
    public function paginatedForUser(int $userId, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: fn($q) => $q->where('user_id', $userId)->latest(),
            relations: ['city:id,name'],
            limit: $limit,
        );
    }

    /**
     * Admin review queue. Optional status filter, newest first, with requester
     * + city + assigned inspector.
     */
    public function paginatedForAdmin(?string $status = null, ?int $limit = null)
    {
        return $this->list(
            callback: function ($q) use ($status) {
                if ($status) {
                    $q->where('status', $status);
                }
                $q->latest();
            },
            relations: ['city:id,name', 'user:id,first_name,last_name,phone_number', 'inspector:id,first_name,last_name'],
        );
    }
}
