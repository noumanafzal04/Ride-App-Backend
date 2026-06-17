<?php
// app/Actions/Driver/DriverOnboardingAction.php

namespace App\Actions\Driver;

use App\Actions\BaseAction\BaseAction;
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

            // 1. flip user type to driver (only if not already)
            if (!$user->isDriver()) {
                $this->repository->update($user->id, ['user_type' => UserType::DRIVER->value]);
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

            if (!empty($profileData)) {
                $this->userProfileRepository->updateOrCreateForUser($user->id, $profileData);
            }

            // 3. upsert driver profile
            $driverData = $data['driver'] ?? [];

            foreach (['cnic_front_image', 'cnic_back_image', 'license_front_image', 'license_back_image'] as $field) {
                if (!empty($driverData[$field])) {
                    $folder = str_contains($field, 'cnic') ? 'driver/cnic' : 'driver/license';
                    $driverData[$field] = $this->imageUploadService->upload(
                        file: $driverData[$field],
                        folder: $folder,
                    );
                }
            }

            if (!empty($driverData)) {
                $this->driverProfileRepository->updateOrCreateForUser($user->id, $driverData);
            }

            // 4. upsert vehicle
            $vehicleData = $data['vehicle'] ?? [];

            if (!empty($vehicleData['vehicle_image'])) {
                $vehicleData['vehicle_image_path'] = $this->imageUploadService->upload(
                    file: $vehicleData['vehicle_image'],
                    folder: 'vehicles',
                );
                unset($vehicleData['vehicle_image']);
            }

            if (!empty($vehicleData)) {
                $this->vehicleRepository->updateOrCreateForUser($user->id, $vehicleData);
            }

            // return user with full relations
            return $data =  $this->repository->query()->with(
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
