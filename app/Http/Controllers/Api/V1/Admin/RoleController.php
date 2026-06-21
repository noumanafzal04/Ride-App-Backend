<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\RoleAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\StoreRoleRequest;
use App\Http\Requests\Api\V1\Admin\UpdateRoleRequest;
use App\Http\Resources\Api\V1\Admin\PermissionResource;
use App\Http\Resources\Api\V1\Admin\RoleResource;
use App\Support\ApiResponse;

class RoleController extends Controller
{
    public function __construct(protected RoleAction $action) {}

    public function index()
    {
        return ApiResponse::success(RoleResource::collection($this->action->list()), 'Roles.');
    }

    /** Permission catalog (grouped client-side by module). */
    public function permissions()
    {
        return ApiResponse::success(PermissionResource::collection($this->action->catalog()), 'Permissions.');
    }

    public function show(int $id)
    {
        return ApiResponse::success(new RoleResource($this->action->show($id)), 'Role.')
            ;
    }

    public function store(StoreRoleRequest $request)
    {
        return ApiResponse::success(new RoleResource($this->action->create($request->validated())), 'Role created.', 201);
    }

    public function update(UpdateRoleRequest $request, int $id)
    {
        return ApiResponse::success(new RoleResource($this->action->update($id, $request->validated())), 'Role updated.');
    }

    public function destroy(int $id)
    {
        $this->action->destroy($id);
        return ApiResponse::noContent('Role deleted.');
    }
}
