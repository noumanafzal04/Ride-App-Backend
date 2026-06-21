<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\ServiceCategoryAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreServiceCategoryRequest;
use App\Http\Requests\Api\V1\Admin\UpdateServiceCategoryRequest;
use App\Http\Resources\Api\V1\Admin\ServiceCategoryResource;
use App\Support\ApiResponse;

class ServiceCategoryController extends Controller
{
    public function __construct(protected ServiceCategoryAction $action) {}

    public function index()
    {
        return ApiResponse::success(ServiceCategoryResource::collection($this->action->list()), 'Service categories.');
    }

    public function store(StoreServiceCategoryRequest $request)
    {
        return ApiResponse::success(new ServiceCategoryResource($this->action->create($request->validated())), 'Category created.', 201);
    }

    public function update(UpdateServiceCategoryRequest $request, int $id)
    {
        return ApiResponse::success(new ServiceCategoryResource($this->action->update($id, $request->validated())), 'Category updated.');
    }

    public function destroy(int $id)
    {
        $this->action->destroy($id);
        return ApiResponse::noContent('Category deleted.');
    }
}
