<?php

namespace App\Repositories\Admin;

use App\Models\ServiceCategory;
use App\Repositories\BaseRepository;

class ServiceCategoryRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new ServiceCategory();
    }

    public function allForAdmin()
    {
        return $this->model->newQuery()->withCount('providers')->orderBy('sort')->get();
    }

    public function nextSort(): int
    {
        return (int) $this->model->newQuery()->max('sort') + 1;
    }

    public function slugExists(string $slug): bool
    {
        return $this->model->newQuery()->where('slug', $slug)->exists();
    }
}
