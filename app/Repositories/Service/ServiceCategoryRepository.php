<?php

namespace App\Repositories\Service;

use App\Models\ServiceCategory;
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
            callback: fn($q) => $q->where('is_active', true)->orderBy('sort'),
        );
    }
}
