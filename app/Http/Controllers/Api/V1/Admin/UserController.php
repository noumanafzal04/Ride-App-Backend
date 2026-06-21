<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Actions\Admin\AppUserAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Admin\SetVerificationRequest;
use App\Http\Resources\Api\V1\Admin\AppUserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function __construct(protected AppUserAction $action) {}

    public function index(Request $request)
    {
        $items = $this->action->list($request->only(['user_type', 'verification', 'search']));

        return ApiResponse::success(AppUserResource::collection($items), 'Users.');
    }

    public function show(int $id)
    {
        return ApiResponse::success(new AppUserResource($this->action->show($id)), 'User.');
    }

    public function setVerification(SetVerificationRequest $request, int $id)
    {
        $user = $this->action->setVerification($id, $request->validated()['status']);

        return ApiResponse::success(new AppUserResource($user), 'Verification updated.');
    }
}
