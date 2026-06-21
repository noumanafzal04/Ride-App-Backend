<?php

namespace App\Repositories\Admin;

use App\Models\DriverProfile;
use App\Models\InspectionRequest;
use App\Models\RideBooking;
use App\Models\RidePost;
use App\Models\ServiceBooking;
use App\Models\ServiceProvider;
use App\Models\User;

class ReportRepository
{
    public function summary(): array
    {
        $usersByType = User::selectRaw('user_type, count(*) c')->groupBy('user_type')->pluck('c', 'user_type');
        $driverVerif = DriverProfile::selectRaw('verification_status, count(*) c')->groupBy('verification_status')->pluck('c', 'verification_status');
        $posts       = RidePost::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $inspections = InspectionRequest::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $providers   = ServiceProvider::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $svc         = ServiceBooking::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        $g = fn($coll, $k) => (int) ($coll[$k] ?? 0);

        return [
            'users' => [
                'total'   => (int) User::count(),
                'drivers' => $g($usersByType, 'driver'),
                'riders'  => $g($usersByType, 'user'),
            ],
            'driver_verification' => [
                'pending'  => $g($driverVerif, 'pending'),
                'verified' => $g($driverVerif, 'verified'),
                'rejected' => $g($driverVerif, 'rejected'),
            ],
            'rides' => [
                'total'              => (int) $posts->sum(),
                'active'             => $g($posts, 'active') + $g($posts, 'full') + $g($posts, 'in_progress'),
                'completed'          => $g($posts, 'completed'),
                'cancelled'          => $g($posts, 'cancelled'),
                'bookings'           => (int) RideBooking::count(),
                'completed_bookings' => (int) RideBooking::where('status', 'completed')->count(),
            ],
            'inspections' => [
                'total'     => (int) $inspections->sum(),
                'pending'   => $g($inspections, 'pending'),
                'reviewing' => $g($inspections, 'reviewing'),
                'scheduled' => $g($inspections, 'scheduled'),
                'completed' => $g($inspections, 'completed'),
                'cancelled' => $g($inspections, 'cancelled'),
            ],
            'providers' => [
                'total'    => (int) $providers->sum(),
                'approved' => $g($providers, 'approved'),
                'pending'  => $g($providers, 'pending'),
                'rejected' => $g($providers, 'rejected'),
            ],
            'services' => [
                'total'     => (int) $svc->sum(),
                'completed' => $g($svc, 'completed'),
                'pending'   => $g($svc, 'requested'),
            ],
        ];
    }
}
