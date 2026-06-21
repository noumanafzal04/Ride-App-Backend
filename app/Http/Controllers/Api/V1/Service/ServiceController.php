<?php

namespace App\Http\Controllers\Api\V1\Service;

use App\Actions\Service\ServiceAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Service\ServiceCategoryResource;

class ServiceController extends Controller
{
    public function __construct(protected ServiceAction $action) {}

    public function categories()
    {
        return ServiceCategoryResource::collection($this->action->categories())
            ->wrapWith('categories')
            ->message('Service categories.');
    }
}
