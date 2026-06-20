<?php

namespace App\Actions\User;

use App\Actions\BaseAction\BaseAction;
use App\Models\User;
use App\Repositories\Auth\UserRepository;
use App\Repositories\User\UserProfileRepository;
use App\Services\Media\ImageUploadService;
use Illuminate\Support\Facades\DB;

class UpdateProfileAction extends BaseAction
{
    public function __construct(
        UserRepository $repository,
        protected UserProfileRepository $userProfileRepository,
        protected ImageUploadService $imageUploadService,
    ) {
        parent::__construct($repository, 'profile');
    }

    public function execute(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {

            // 1. Name (users table)
            $userData = [];
            if (array_key_exists('first_name', $data)) $userData['first_name'] = $data['first_name'];
            if (array_key_exists('last_name', $data))  $userData['last_name']  = $data['last_name'];
            if (!empty($userData)) {
                $this->repository->update($user->id, $userData);
            }

            // 2. Basic info (user_profiles)
            $profileData = $data['profile'] ?? [];

            // Drivers can't change their photo (it's tied to verification).
            if ($user->isDriver()) {
                unset($profileData['profile_image']);
            } elseif (!empty($profileData['profile_image'])) {
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

            // 3. Return the fresh user with the same relations as /auth/me
            $fresh = $this->repository->query()->findOrFail($user->id);
            $fresh->load('profile');
            if ($fresh->isDriver()) {
                $fresh->load(['driverProfile', 'vehicles.vehicleModel.make']);
            }

            return $fresh;
        });
    }
}
