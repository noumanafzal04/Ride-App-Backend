<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Actions\Driver\DriverOnboardingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Driver\DriverOnboardingRequest;
use App\Http\Resources\Api\V1\Driver\DriverOnboardingResource;

class DriverProfileController extends Controller
{
    public $resourceName = 'driver_onboarding';

    public function __construct(protected DriverOnboardingAction $action) {}

    public function onboard(DriverOnboardingRequest $request)
    {
        $user = $this->action->execute(
            auth()->user(),
            $request->validated()
        );

        return (new DriverOnboardingResource($user))
            ->message(__("{$this->resourceName}.completed"));
    }
}
