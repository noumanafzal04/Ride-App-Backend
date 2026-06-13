<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Actions\Driver\DriverOnboardingAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Driver\DriverOnboardingRequest;
use App\Http\Resources\Api\V1\Driver\DriverOnboardingResource;
use App\Support\ApiResponse;

class DriverProfileController extends Controller
{
    public function __construct(
        protected DriverOnboardingAction $onboardingAction,
    ) {}

    public function onboard(DriverOnboardingRequest $request)
    {
        $user = $this->onboardingAction->execute(
            auth()->user(),
            $request->validated()
        );

        return (new DriverOnboardingResource($user))
            ->message('Driver onboarding completed successfully.');
    }
}
