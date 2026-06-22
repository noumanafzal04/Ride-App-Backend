<?php

namespace App\Repositories\Admin;

use App\Models\CarListing;
use App\Models\DriverProfile;
use App\Models\InspectionRequest;
use App\Models\RideBooking;
use App\Models\RidePost;
use App\Models\ServiceBooking;
use App\Models\ServiceProvider;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;

class ReportRepository
{
    public function summary(?string $from = null, ?string $to = null): array
    {
        $start = ($from ? Carbon::parse($from) : Carbon::today()->subDays(29))->startOfDay();
        $end   = ($to ? Carbon::parse($to) : Carbon::today())->endOfDay();

        // All-time status breakdowns (for the donuts / health view).
        $usersByType = User::selectRaw('user_type, count(*) c')->groupBy('user_type')->pluck('c', 'user_type');
        $driverVerif = DriverProfile::selectRaw('verification_status, count(*) c')->groupBy('verification_status')->pluck('c', 'verification_status');
        $posts       = RidePost::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $inspections = InspectionRequest::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $providers   = ServiceProvider::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $svc         = ServiceBooking::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');
        $listings    = CarListing::selectRaw('status, count(*) c')->groupBy('status')->pluck('c', 'status');

        $g = fn($coll, $k) => (int) ($coll[$k] ?? 0);
        $inRange = fn(Builder $q) => (int) $q->whereBetween('created_at', [$start, $end])->count();

        // Daily buckets if the span is small, monthly otherwise.
        $spanDays    = $start->diffInDays($end) + 1;
        $granularity = $spanDays > 62 ? 'month' : 'day';

        return [
            'range' => [
                'from'        => $start->toDateString(),
                'to'          => $end->toDateString(),
                'granularity' => $granularity,
            ],

            // New activity within the selected range.
            'period' => [
                'new_users'            => $inRange(User::query()),
                'new_rides'            => $inRange(RidePost::query()),
                'new_bookings'         => $inRange(RideBooking::query()),
                'completed_bookings'   => (int) RideBooking::where('status', 'completed')->whereBetween('updated_at', [$start, $end])->count(),
                'new_inspections'      => $inRange(InspectionRequest::query()),
                'new_providers'        => $inRange(ServiceProvider::query()),
                'new_listings'         => $inRange(CarListing::query()),
                'new_service_bookings' => $inRange(ServiceBooking::query()),
            ],

            // Time series for the trend chart.
            'trend' => $this->trend($start, $end, $granularity),

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
            'listings' => [
                'total'   => (int) $listings->sum(),
                'active'  => $g($listings, 'active'),
                'pending' => $g($listings, 'pending'),
                'sold'    => $g($listings, 'sold'),
            ],
        ];
    }

    // Per-bucket new users / rides / bookings / listings across the range.
    protected function trend(Carbon $start, Carbon $end, string $granularity): array
    {
        $fmtSql = $granularity === 'month' ? '%Y-%m' : '%Y-%m-%d';

        $series = fn(string $model) => $model::query()
            ->selectRaw("DATE_FORMAT(created_at, '{$fmtSql}') as bucket, count(*) c")
            ->whereBetween('created_at', [$start, $end])
            ->groupBy('bucket')->pluck('c', 'bucket');

        $users    = $series(User::class);
        $rides    = $series(RidePost::class);
        $bookings = $series(RideBooking::class);
        $listings = $series(CarListing::class);

        // Build the full bucket list so the chart has no gaps.
        $out = [];
        $cursor = $start->copy();
        $step = $granularity === 'month' ? 'addMonth' : 'addDay';
        $keyFmt = $granularity === 'month' ? 'Y-m' : 'Y-m-d';
        $labelFmt = $granularity === 'month' ? 'M Y' : 'd M';

        while ($cursor->lte($end)) {
            $key = $cursor->format($keyFmt);
            $out[] = [
                'label'    => $cursor->format($labelFmt),
                'users'    => (int) ($users[$key] ?? 0),
                'rides'    => (int) ($rides[$key] ?? 0),
                'bookings' => (int) ($bookings[$key] ?? 0),
                'listings' => (int) ($listings[$key] ?? 0),
            ];
            $cursor->$step();
            if (count($out) > 366) break; // safety
        }

        return $out;
    }
}
