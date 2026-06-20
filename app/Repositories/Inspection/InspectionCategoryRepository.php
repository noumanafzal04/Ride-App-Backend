<?php

namespace App\Repositories\Inspection;

use App\Models\InspectionCategory;
use App\Repositories\BaseRepository;

class InspectionCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new InspectionCategory();
    }

    public function allOrdered()
    {
        return $this->list(callback: fn($q) => $q->orderBy('sort'));
    }

    public function existingIds(): array
    {
        return $this->model->newQuery()->pluck('id')->all();
    }
}
