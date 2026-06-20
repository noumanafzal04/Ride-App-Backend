<?php

namespace App\Repositories\Inspection;

use App\Models\InspectionCategoryResult;
use App\Repositories\BaseRepository;

class InspectionCategoryResultRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new InspectionCategoryResult();
    }

    public function forRequest(int $requestId)
    {
        return $this->list(
            callback: fn($q) => $q->where('inspection_request_id', $requestId),
            relations: ['category:id,name,slug,sort'],
        );
    }

    public function upsertForRequest(int $requestId, int $categoryId, string $condition, ?string $notes): void
    {
        $this->updateOrCreate(
            ['inspection_request_id' => $requestId, 'category_id' => $categoryId],
            ['condition' => $condition, 'notes' => $notes],
        );
    }
}
