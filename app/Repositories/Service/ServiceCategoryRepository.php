<?php

namespace App\Repositories\Service;

use App\Models\ServiceCategory;
use App\Models\ServiceProvider;
use App\Repositories\BaseRepository;

class ServiceCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ServiceCategory();
    }

    public function allActive()
    {
        return $this->list(
            callback: fn($q) => $q
                ->where('is_active', true)
                // Count only APPROVED providers offering each category.
                ->withCount(['providers as providers_count' => fn($p) => $p->where('status', ServiceProvider::STATUS_APPROVED)])
                ->orderBy('sort'),
        );
    }
}
