<?php

namespace App\Observers;

use App\Models\DriverProfile;
use App\Services\Notification\AdminNotificationService;
use App\Services\Notification\NotificationService;

class DriverProfileObserver
{
    public function __construct(
        protected NotificationService $notifications,
        protected AdminNotificationService $adminNotifications,
    ) {}

    /** New driver profile → notify admins to verify. */
    public function created(DriverProfile $profile): void
    {
        $this->adminNotifications->push(
            'driver_pending',
            'New driver to verify',
            'A driver submitted documents for verification.',
            ['user_id' => $profile->user_id],
        );
    }

    /**
     * Notify the driver when their account becomes verified.
     * Fires on Eloquent model saves (not raw query-builder updates).
     */
    public function updated(DriverProfile $profile): void
    {
        if ($profile->wasChanged('verification_status') && $profile->verification_status === 'verified') {
            $this->notifications->push(
                $profile->user_id,
                'driver_verified',
                'You’re verified',
                'Your driver account is verified — you can now post rides.',
                [],
            );
        }
    }
}
