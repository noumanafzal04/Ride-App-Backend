<?php

namespace App\Actions\Admin;

use App\Exceptions\ApiException;
use App\Models\User;
use App\Repositories\Admin\AppUserRepository;
use App\Repositories\Driver\DriverProfileRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class AppUserAction
{
    public function __construct(
        protected AppUserRepository $repository,
        protected DriverProfileRepository $driverProfiles,
        protected NotificationService $notifications,
    ) {}

    public function list(array $filters = [], ?int $perPage = null)
    {
        return $this->repository->paginatedForAdmin($filters, $perPage);
    }

    public function show(int $id): User
    {
        return $this->repository->findDetail($id);
    }

    /**
     * Set a driver's profile verification status.
     * verified → DriverProfileObserver fires the driver_verified notification.
     * rejected → we notify the driver here.
     */
    public function setVerification(int $userId, string $status): User
    {
        return DB::transaction(function () use ($userId, $status) {
            $profile = $this->driverProfiles->forUser($userId);
            if (!$profile) {
                throw new ApiException('This user has no driver profile to verify.', 422);
            }

            $this->driverProfiles->updateVerification($profile, $status);

            if ($status === 'rejected') {
                $this->notifications->push(
                    $userId,
                    'driver_rejected',
                    'Verification rejected',
                    'Your driver verification was rejected. Please review your documents and resubmit.',
                    [],
                );
            }

            return $this->repository->findDetail($userId);
        });
    }
}
