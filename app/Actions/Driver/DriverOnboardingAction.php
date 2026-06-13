<?php

namespace App\Actions\Driver;

use App\Enums\UserType\UserType;
use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\User\UserProfileRepository;
use App\Repositories\Vehicle\VehicleRepository;
use App\Services\Media\ImageUploadService;
use Illuminate\Support\Facades\DB;

class DriverOnboardingAction
{
    public function __construct(
        protected UserRepository          $userRepository,
        protected UserProfileRepository   $userProfileRepository,
        protected DriverProfileRepository $driverProfileRepository,
        protected VehicleRepository       $vehicleRepository,
        protected ImageUploadService      $imageUploadService,
    ) {}

    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            // 1. flip user type to driver
            $this->userRepository->update(
                $user->id,
                ['user_type' => UserType::DRIVER->value]
            );

            // 2. upsert user profile
            $profileData = $data['profile'] ?? [];

            if (!empty($profileData['profile_photo'])) {
                $profileData['profile_photo'] = $this->imageUploadService->upload(
                    file: $profileData['profile_photo'],
                    folder: 'profiles',
                    quality: 80,
                    maxWidth: 400
                );
            }

            $this->userProfileRepository->updateOrCreateForUser($user->id, $profileData);

            // 3. guard duplicate driver profile
            if ($this->driverProfileRepository->findByUserId($user->id)) {
                throw new ApiException('Driver profile already exists.', 422);
            }

            // 4. upload driver credential images then create profile
            $driverData = $data['driver'];

            $driverData['cnic_front_image'] = $this->imageUploadService->upload(
                file: $driverData['cnic_front_image'],
                folder: 'driver/cnic',
            );

            $driverData['cnic_back_image'] = $this->imageUploadService->upload(
                file: $driverData['cnic_back_image'],
                folder: 'driver/cnic',
            );

            $driverData['license_front_image'] = $this->imageUploadService->upload(
                file: $driverData['license_front_image'],
                folder: 'driver/license',
            );

            $driverData['license_back_image'] = $this->imageUploadService->upload(
                file: $driverData['license_back_image'],
                folder: 'driver/license',
            );

            $this->driverProfileRepository->createForUser($user->id, $driverData);

            // 5. upload vehicle image then create vehicle
            $vehicleData = $data['vehicle'];

            $vehicleData['vehicle_image_path'] = $this->imageUploadService->upload(
                file: $vehicleData['vehicle_image'],
                folder: 'vehicles',
            );

            // remove the raw file key — not a DB column
            unset($vehicleData['vehicle_image']);

            $this->vehicleRepository->createForUser($user->id, $vehicleData);

            return $user->fresh->load(['profile', 'driverProfile', 'vehicles']);
        });
    }
}
