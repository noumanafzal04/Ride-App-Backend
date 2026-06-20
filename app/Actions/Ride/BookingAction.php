<?php

namespace App\Actions\Ride;

use App\Actions\BaseAction\BaseAction;
use App\Exceptions\ApiException;
use App\Models\RideBooking;
use App\Models\RidePost;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\BookingRepository;
use App\Repositories\Ride\RatingRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class BookingAction extends BaseAction
{
    public function __construct(
        BookingRepository $repository,
        protected RidePostRepository $ridePostRepository,
        protected RatingRepository $ratingRepository,
        protected DriverProfileRepository $driverProfileRepository,
        protected NotificationService $notifications,
    ) {
        parent::__construct($repository, 'ride_booking');
    }

    /**
     * Driver starts the ride → locks it as in_progress so riders can no longer
     * cancel mid-trip. Requires at least one accepted passenger.
     */
    public function startRide(int $driverId, int $ridePostId): RidePost
    {
        return DB::transaction(function () use ($driverId, $ridePostId) {

            $post = $this->ridePostRepository->findActiveForBooking($ridePostId); // locked

            if ($post->driver_id !== $driverId) {
                throw new ApiException('You do not own this ride.', 403);
            }

            if (!in_array($post->status, ['active', 'full'])) {
                throw new ApiException('This ride can no longer be started.', 422);
            }

            $hasAccepted = $this->repository->findOne(
                callback: fn($q) => $q
                    ->where('ride_post_id', $ridePostId)
                    ->where('status', 'accepted')
            );

            if (!$hasAccepted) {
                throw new ApiException('You have no accepted passengers to start the ride.', 422);
            }

            $this->ridePostRepository->update($post->id, ['status' => 'in_progress']);

            // Tell every confirmed rider the ride has begun.
            $accepted = $this->repository->list(
                callback: fn($q) => $q
                    ->where('ride_post_id', $ridePostId)
                    ->where('status', 'accepted')
            );
            foreach ($accepted as $b) {
                $this->notifications->push(
                    $b->passenger_id,
                    'ride_started',
                    'Ride started',
                    'Your driver has started the ride. Enjoy your trip!',
                    ['ride_post_id' => $ridePostId, 'booking_id' => $b->id],
                );
            }

            return $this->ridePostRepository->findOrFail($ridePostId);
        });
    }

    /**
     * Driver ends the ride in ONE action: every accepted booking is completed
     * (a trip counted for each), any leftover pending requests are rejected, and
     * the post is closed regardless of unsold seats. Both parties can then review.
     */
    public function endRide(int $driverId, int $ridePostId): RidePost
    {
        return DB::transaction(function () use ($driverId, $ridePostId) {

            $post = $this->ridePostRepository->findActiveForBooking($ridePostId); // locked

            if ($post->driver_id !== $driverId) {
                throw new ApiException('You do not own this ride.', 403);
            }

            if (in_array($post->status, ['completed', 'cancelled'])) {
                throw new ApiException('This ride is already closed.', 422);
            }

            $this->settleRide($post);

            return $this->ridePostRepository->findOrFail($ridePostId);
        });
    }

    /**
     * System safety net: close any ride left open past departure + grace hours,
     * so a driver who forgets to end a ride is never blocked from posting again.
     * Returns how many rides were closed.
     */
    public function autoCloseStale(int $graceHours = 2): int
    {
        $stale = $this->ridePostRepository->staleOpenRides($graceHours);

        foreach ($stale as $post) {
            DB::transaction(fn() => $this->settleRide($post));
        }

        return $stale->count();
    }

    /**
     * Settle a ride: complete its accepted bookings (count a trip each), reject
     * any leftover pending requests, and close the post. A ride nobody joined is
     * marked cancelled rather than completed.
     */
    protected function settleRide(RidePost $post): void
    {
        $accepted = $this->repository->list(
            callback: fn($q) => $q
                ->where('ride_post_id', $post->id)
                ->where('status', 'accepted')
        );

        foreach ($accepted as $booking) {
            $this->repository->update($booking->id, ['status' => 'completed']);
            $this->driverProfileRepository->incrementTripsForUser($post->driver_id);
            $this->notifications->push(
                $booking->passenger_id,
                'ride_completed',
                'Ride completed',
                'Your ride is complete. Please rate your driver.',
                ['ride_post_id' => $post->id, 'booking_id' => $booking->id],
            );
        }

        // Decline + notify any still-pending requests (never accepted before the
        // ride ended/auto-closed) — don't leave the rider hanging.
        $pending = $this->repository->list(
            callback: fn($q) => $q
                ->where('ride_post_id', $post->id)
                ->where('status', 'pending')
        );
        foreach ($pending as $req) {
            $this->repository->update($req->id, ['status' => 'rejected']);
            $this->notifications->push(
                $req->passenger_id,
                'booking_rejected',
                'Request not accepted',
                'The ride ended before your request was accepted.',
                ['ride_post_id' => $post->id, 'booking_id' => $req->id],
            );
        }

        $this->ridePostRepository->update($post->id, [
            'status' => $accepted->isNotEmpty() ? 'completed' : 'cancelled',
        ]);
    }

    /**
     * Either party leaves an optional review after completion.
     */
    public function rate(int $userId, int $bookingId, array $data)
    {
        return DB::transaction(function () use ($userId, $bookingId, $data) {

            $booking = $this->repository->findOrFail($bookingId, relations: ['ridePost']);
            $this->guardParty($userId, $booking);

            if ($booking->status !== 'completed') {
                throw new ApiException('You can review only after the ride is completed.', 422);
            }

            $driverId    = $booking->ridePost->driver_id;
            $passengerId = $booking->passenger_id;
            $isPassenger = $userId === $passengerId;

            $toUserId = $isPassenger ? $driverId : $passengerId;
            $ratedAs  = $isPassenger ? 'driver' : 'passenger';

            $already = $this->ratingRepository->findOne(
                callback: fn($q) => $q
                    ->where('rateable_type', RideBooking::class)
                    ->where('rateable_id', $booking->id)
                    ->where('from_user_id', $userId)
            );

            if ($already) {
                throw new ApiException('You have already reviewed this ride.', 422);
            }

            $rating = $this->ratingRepository->create([
                'type'          => 'ride',
                'rateable_type' => RideBooking::class,
                'rateable_id'   => $booking->id,
                'from_user_id'  => $userId,
                'to_user_id'    => $toUserId,
                'rated_as'      => $ratedAs,
                'rating'        => (int) $data['rating'],
                'review'        => $data['review'] ?? null,
            ]);

            // Keep the driver's average rating in sync
            if ($ratedAs === 'driver') {
                $avg = $this->ratingRepository->avgForUser($toUserId, 'ride');
                $this->driverProfileRepository->setRatingAvgForUser($toUserId, $avg);
            }

            // Let the rated person know they got a review.
            $this->notifications->push(
                $toUserId,
                'review_received',
                'New review',
                "You received a {$rating->rating}-star review.",
                ['ride_post_id' => $booking->ride_post_id, 'booking_id' => $booking->id],
            );

            return $rating;
        });
    }

    public function book(int $passengerId, int $ridePostId, array $data): RideBooking
    {
        return DB::transaction(function () use ($passengerId, $ridePostId, $data) {

            // fetch via repository — no model in action
            $ridePost = $this->ridePostRepository->findOrFail($ridePostId);

            if ($ridePost->driver_id === $passengerId) {
                throw new ApiException('You cannot book your own ride.', 422);
            }

            if ($ridePost->status !== 'active') {
                throw new ApiException('This ride is no longer available.', 422);
            }

            if ($ridePost->departure_at && $ridePost->departure_at->isPast()) {
                throw new ApiException('This ride has already departed.', 422);
            }

            // A rider may send requests to several drivers at once (so they don't
            // wait on one who may never accept), but can hold only ONE confirmed
            // ride. Block only if they already have an accepted booking.
            $confirmed = $this->repository->findOne(
                callback: fn($q) => $q
                    ->where('passenger_id', $passengerId)
                    ->where('status', 'accepted')
            );

            if ($confirmed) {
                throw new ApiException('You already have a confirmed ride. Complete or cancel it before booking another.', 422);
            }

            $seats = (int) $data['seats'];

            if (
                $ridePost->post_type === 'shared'
                && ($ridePost->available_seats === null || $seats > $ridePost->available_seats)
            ) {
                throw new ApiException('Not enough seats available.', 422);
            }

            $payload = [
                'ride_post_id'   => $ridePostId,
                'passenger_id'   => $passengerId,
                'seats_booked'   => $seats,
                'price_per_seat' => $ridePost->price_per_seat,
                'total_amount'   => $seats * $ridePost->price_per_seat,
                'note'           => $data['note'] ?? null,
                'status'         => 'pending',
            ];

            // A rider can have only ONE row per ride (unique constraint). If a prior
            // booking exists, decide based on its status:
            $exists = $this->repository->findOne(
                callback: fn($q) => $q
                    ->where('ride_post_id', $ridePostId)
                    ->where('passenger_id', $passengerId)
            );

            if ($exists) {
                if (in_array($exists->status, ['pending', 'accepted'])) {
                    throw new ApiException('You already have a booking request for this ride.', 422);
                }
                if ($exists->status === 'completed') {
                    throw new ApiException('You have already taken this ride.', 422);
                }
                // Previously cancelled (by rider) or declined (by driver) → let them
                // send a fresh request by reopening the same row.
                $this->repository->update($exists->id, $payload);
                $booking = $this->repository->findOrFail($exists->id);
            } else {
                $booking = $this->repository->create($payload);
            }

            $this->notifications->push(
                $ridePost->driver_id,
                'booking_requested',
                'New booking request',
                'A rider requested ' . $seats . ' seat' . ($seats > 1 ? 's' : '') . ' on your ride.',
                ['ride_post_id' => $ridePostId, 'booking_id' => $booking->id],
            );

            return $booking;
        });
    }

    public function driverBookings(int $driverId, array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($driverId, $filters) {
                $query->whereHas(
                    'ridePost',
                    fn($q) => $q->where('driver_id', $driverId)
                );

                // Scope to one post (driver's current active ride) so old
                // declined/cancelled requests from past rides don't clutter it.
                if (!empty($filters['ride_post_id'])) {
                    $query->where('ride_post_id', $filters['ride_post_id']);
                }

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                $query->latest();
            },
            relations: [
                'passenger:id,first_name,last_name,phone_number',
                'ridePost:id,driver_id,from_city_id,to_city_id,departure_at,post_type,status',
                'ridePost.fromCity:id,name',
                'ridePost.toCity:id,name',
                'ratings',
            ],
        );
    }

    public function riderBookings(int $passengerId, array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($passengerId, $filters) {
                $query->where('passenger_id', $passengerId);

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                $query->latest();
            },
            relations: [
                'ridePost:id,driver_id,from_city_id,to_city_id,departure_at,post_type,price_per_seat,status',
                'ridePost.driver:id,first_name,last_name,phone_number',
                'ridePost.fromCity:id,name',
                'ridePost.toCity:id,name',
                'ratings',
            ],
        );
    }

    public function cancel(int $passengerId, int $bookingId): RideBooking
    {
        return DB::transaction(function () use ($passengerId, $bookingId) {

            $booking = $this->repository->findOrFail($bookingId, relations: ['ridePost']);

            if ($booking->passenger_id !== $passengerId) {
                throw new ApiException('You do not own this booking.', 403);
            }

            // A rider can cancel anytime until the ride is actually completed — the
            // driver may mark "started" before reaching them, so they must not be
            // locked in. Only a completed/cancelled/rejected booking can't cancel.
            if (!in_array($booking->status, ['pending', 'accepted'])) {
                throw new ApiException('This booking can no longer be cancelled.', 422);
            }

            // Release the reserved seat. Re-open the post for booking only if it
            // hadn't started yet (don't downgrade an in-progress ride to active).
            if ($booking->status === 'accepted') {
                $post = $this->ridePostRepository->findActiveForBooking($booking->ride_post_id);

                $updates = [];
                if ($post->post_type === 'shared') {
                    $updates['available_seats'] = ($post->available_seats ?? 0) + $booking->seats_booked;
                }
                if ($post->status === 'full') {
                    $updates['status'] = 'active';
                }
                if (!empty($updates)) {
                    $this->ridePostRepository->update($post->id, $updates);
                }
            }

            $this->repository->update($booking->id, ['status' => 'cancelled']);

            if ($booking->ridePost) {
                $this->notifications->push(
                    $booking->ridePost->driver_id,
                    'booking_cancelled',
                    'Booking cancelled',
                    'A rider cancelled their booking on your ride.',
                    ['ride_post_id' => $booking->ride_post_id, 'booking_id' => $booking->id],
                );
            }

            return $this->repository->findOrFail($bookingId, relations: ['ridePost']);
        });
    }

    public function accept(int $driverId, int $bookingId): RideBooking
    {
        return DB::transaction(function () use ($driverId, $bookingId) {

            // fetch via repository
            $booking = $this->repository->findOrFail(
                $bookingId,
                relations: ['ridePost']
            );

            $this->guardOwner($driverId, $booking);

            if ($booking->status !== 'pending') {
                throw new ApiException('Only pending bookings can be accepted.', 422);
            }

            // lock the ride post row via repository
            $post = $this->ridePostRepository->findActiveForBooking($booking->ride_post_id);

            // Once a ride is full / started / closed it no longer takes bookings.
            if ($post->status !== 'active') {
                throw new ApiException('This ride is no longer accepting bookings.', 422);
            }

            $becameFull = false;

            if ($post->post_type === 'shared') {
                if ($post->available_seats < $booking->seats_booked) {
                    throw new ApiException('Not enough seats left to accept this booking.', 422);
                }

                $newSeats   = $post->available_seats - $booking->seats_booked;
                $becameFull = $newSeats <= 0;

                $this->ridePostRepository->update($post->id, [
                    'available_seats' => $newSeats,
                    'status'          => $becameFull ? 'full' : $post->status,
                ]);
            } else {
                // Private posts book the whole vehicle → full on first accept.
                $becameFull = true;
                $this->ridePostRepository->update($post->id, ['status' => 'full']);
            }

            $this->repository->update($booking->id, ['status' => 'accepted']);

            $this->notifications->push(
                $booking->passenger_id,
                'booking_accepted',
                'Booking accepted',
                'Good news — your booking was accepted. Get ready for your ride!',
                ['ride_post_id' => $post->id, 'booking_id' => $booking->id],
            );

            // This rider is now confirmed → withdraw their pending requests to
            // other drivers (they were waiting for whoever accepted first).
            $otherPendings = $this->repository->list(
                callback: fn($q) => $q
                    ->where('passenger_id', $booking->passenger_id)
                    ->where('status', 'pending')
                    ->where('id', '!=', $booking->id),
                relations: ['ridePost'],
            );
            foreach ($otherPendings as $op) {
                $this->repository->update($op->id, ['status' => 'cancelled']);
                if ($op->ridePost) {
                    $this->notifications->push(
                        $op->ridePost->driver_id,
                        'booking_cancelled',
                        'Request withdrawn',
                        'A rider cancelled their request — they booked another ride.',
                        ['ride_post_id' => $op->ride_post_id, 'booking_id' => $op->id],
                    );
                }
            }

            // No seats remain → decline + notify any other outstanding requests.
            if ($becameFull) {
                $others = $this->repository->list(
                    callback: fn($q) => $q
                        ->where('ride_post_id', $post->id)
                        ->where('status', 'pending')
                );
                $this->repository->updateByConditions(
                    ['ride_post_id' => $post->id, 'status' => 'pending'],
                    ['status' => 'rejected']
                );
                foreach ($others as $o) {
                    $this->notifications->push(
                        $o->passenger_id,
                        'booking_rejected',
                        'Ride full',
                        'This ride is now full, so your request could not be accepted.',
                        ['ride_post_id' => $post->id, 'booking_id' => $o->id],
                    );
                }
            }

            return $this->repository->findOrFail($bookingId, relations: ['ridePost']);
        });
    }

    public function reject(int $driverId, int $bookingId): RideBooking
    {
        return DB::transaction(function () use ($driverId, $bookingId) {

            $booking = $this->repository->findOrFail(
                $bookingId,
                relations: ['ridePost']
            );

            $this->guardOwner($driverId, $booking);

            if ($booking->status !== 'pending') {
                throw new ApiException('Only pending bookings can be rejected.', 422);
            }

            $this->repository->update($booking->id, ['status' => 'rejected']);

            $this->notifications->push(
                $booking->passenger_id,
                'booking_rejected',
                'Booking declined',
                'Your booking request was declined by the driver.',
                ['ride_post_id' => $booking->ride_post_id, 'booking_id' => $booking->id],
            );

            return $this->repository->findOrFail($bookingId);
        });
    }

    protected function guardOwner(int $driverId, RideBooking $booking): void
    {
        if ($booking->ridePost?->driver_id !== $driverId) {
            throw new ApiException('You do not own this booking.', 403);
        }
    }

    protected function guardParty(int $userId, RideBooking $booking): void
    {
        $isPassenger = $booking->passenger_id === $userId;
        $isDriver    = $booking->ridePost?->driver_id === $userId;

        if (!$isPassenger && !$isDriver) {
            throw new ApiException('You are not part of this booking.', 403);
        }
    }
}
