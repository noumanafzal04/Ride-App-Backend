<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\StaffAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreStaffRequest;
use App\Http\Requests\Api\V1\Admin\UpdateStaffRequest;
use App\Http\Resources\Api\V1\Admin\StaffResource;
use App\Support\ApiResponse;

class StaffController extends Controller
{
    public function __construct(protected StaffAction $action) {}

    public function index()
    {
        return ApiResponse::success(StaffResource::collection($this->action->list()), 'Staff.');
    }

    public function store(StoreStaffRequest $request)
    {
        return ApiResponse::success(new StaffResource($this->action->create($request->validated())), 'Staff created.', 201);
    }

    public function update(UpdateStaffRequest $request, int $id)
    {
        return ApiResponse::success(new StaffResource($this->action->update($id, $request->validated())), 'Staff updated.');
    }

    public function destroy(int $id)
    {
        $this->action->destroy($id, auth()->id());
        return ApiResponse::noContent('Staff deleted.');
    }
}
