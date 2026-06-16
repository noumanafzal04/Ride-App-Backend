<?php

namespace App\Repositories\Driver;

use App\Models\RidePost;
use App\Repositories\BaseRepository;


class RidePostRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new RidePost();
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
