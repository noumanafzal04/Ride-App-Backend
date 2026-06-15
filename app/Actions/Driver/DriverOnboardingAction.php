<?php
// app/Actions/Driver/DriverOnboardingAction.php

namespace App\Actions\Driver;

use App\Actions\BaseAction\BaseAction;
use App\Constants\ResourceFields;
use App\Enums\UserType\UserType;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\User\UserProfileRepository;
use App\Repositories\Vehicle\VehicleRepository;
use App\Services\Media\ImageUploadService;
use App\Support\BuildsWithRelations;
use Illuminate\Support\Facades\DB;

class DriverOnboardingAction extends BaseAction
{
    public function __construct(
        UserRepository $repository,
        protected UserProfileRepository   $userProfileRepository,
        protected DriverProfileRepository $driverProfileRepository,
        protected VehicleRepository       $vehicleRepository,
        protected ImageUploadService      $imageUploadService,
    ) {
        parent::__construct($repository, 'driver_onboarding');
    }

    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            // 1. flip user type to driver
            $updated = $this->repository->update(
                $user->id,
                ['user_type' => UserType::DRIVER->value]
            );

            if (!$updated) {
                throw new ApiException(__("{$this->resourceName}.update_failed"), 422);
            }

            // 2. upsert user profile
            $profileData = $data['profile'] ?? [];

            if (!empty($profileData['profile_image'])) {
                $profileData['profile_image'] = $this->imageUploadService->upload(
                    file: $profileData['profile_image'],
                    folder: 'profiles',
                    quality: 80,
                    maxWidth: 400,
                );
            }

            $this->userProfileRepository->updateOrCreateForUser($user->id, $profileData);

            // 3. guard duplicate driver profile
            if ($this->driverProfileRepository->existsForUser($user->id)) {
                throw new ApiException(__("{$this->resourceName}.already_exists"), 422);
            }

            // 4. upload driver credential images
            $driverData = $data['driver'];

            foreach (['cnic_front_image', 'cnic_back_image', 'license_front_image', 'license_back_image'] as $field) {
                $folder = str_contains($field, 'cnic') ? 'driver/cnic' : 'driver/license';
                $driverData[$field] = $this->imageUploadService->upload(
                    file: $driverData[$field],
                    folder: $folder,
                );
            }

            $this->driverProfileRepository->createForUser($user->id, $driverData);

            // 5. upload vehicle image then create vehicle
            $vehicleData = $data['vehicle'];

            $vehicleData['vehicle_image_path'] = $this->imageUploadService->upload(
                file: $vehicleData['vehicle_image'],
                folder: 'vehicles',
            );

            unset($vehicleData['vehicle_image']);

            $this->vehicleRepository->createForUser($user->id, $vehicleData);

            // return user with full relations using BuildsWithRelations
            return $this->repository->query()->with(
                BuildsWithRelations::relations(
                    User::RESOURCE_RELATIONS,
                    [
                        'profile',
                        'driverProfile',
                        'vehicles',
                        'vehicles.vehicleModel',
                        'vehicles.vehicleModel.make',
                    ]
                )
            )->findOrFail($user->id);
        });
    }
}
