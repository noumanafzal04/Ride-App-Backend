<?php

namespace App\Http\Controllers\Api\V1\Profile;

use App\Actions\User\UpdateProfileAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\User\UpdateProfileRequest;
use App\Http\Resources\Api\V1\Auth\UserResource;

class ProfileController extends Controller
{
    public $resourceName = 'profile';

    public function __construct(protected UpdateProfileAction $action) {}

    public function update(UpdateProfileRequest $request)
    {
        $user = $this->action->execute(auth()->user(), $request->validated());

        return (new UserResource($user))
            ->message(__("{$this->resourceName}.updated"));
    }
}
