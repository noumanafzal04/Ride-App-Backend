<?php

namespace App\Actions\Ride;

use App\Actions\BaseAction\BaseAction;
use App\Exceptions\ApiException;
use App\Models\RideBooking;
use App\Repositories\Driver\DriverProfileRepository;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\BookingRepository;
use App\Repositories\Ride\RatingRepository;
use Illuminate\Support\Facades\DB;

class BookingAction extends BaseAction
{
    public function __construct(
        BookingRepository $repository,
        protected RidePostRepository $ridePostRepository,
        protected RatingRepository $ratingRepository,
        protected DriverProfileRepository $driverProfileRepository,
    ) {
        parent::__construct($repository, 'ride_booking');
    }

    /**
     * Either party marks the booking completed (after departure time).
     */
    public function complete(int $userId, int $bookingId): RideBooking
    {
        return DB::transaction(function () use ($userId, $bookingId) {

            $booking = $this->repository->findOrFail($bookingId, relations: ['ridePost']);
            $this->guardParty($userId, $booking);

            if ($booking->status !== 'accepted') {
                throw new ApiException('Only an accepted booking can be completed.', 422);
            }

            if ($booking->ridePost?->departure_at && $booking->ridePost->departure_at->isFuture()) {
                throw new ApiException('You can complete the ride after its departure time.', 422);
            }

            $this->repository->update($booking->id, ['status' => 'completed']);

            // Count the trip for the driver
            if ($booking->ridePost) {
                $this->driverProfileRepository->incrementTripsForUser($booking->ridePost->driver_id);
            }

            // When no accepted bookings remain, the whole post is done → driver can repost
            $stillAccepted = $this->repository->findOne(
                callback: fn($q) => $q
                    ->where('ride_post_id', $booking->ride_post_id)
                    ->where('status', 'accepted')
            );
            if (!$stillAccepted) {
                $this->ridePostRepository->update($booking->ride_post_id, ['status' => 'completed']);
            }

            return $this->repository->findOrFail($bookingId, relations: ['ridePost', 'ratings']);
        });
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

            $seats = (int) $data['seats'];

            if (
                $ridePost->post_type === 'shared'
                && ($ridePost->available_seats === null || $seats > $ridePost->available_seats)
            ) {
                throw new ApiException('Not enough seats available.', 422);
            }

            // duplicate booking check via repository
            $exists = $this->repository->findOne(
                callback: fn($q) => $q
                    ->where('ride_post_id', $ridePostId)
                    ->where('passenger_id', $passengerId)
            );

            if ($exists) {
                throw new ApiException('You already have a booking request for this ride.', 422);
            }

            return $this->repository->create([
                'ride_post_id'   => $ridePostId,
                'passenger_id'   => $passengerId,
                'seats_booked'   => $seats,
                'price_per_seat' => $ridePost->price_per_seat,
                'total_amount'   => $seats * $ridePost->price_per_seat,
                'note'           => $data['note'] ?? null,
                'status'         => 'pending',
            ]);
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

                if (!empty($filters['status'])) {
                    $query->where('status', $filters['status']);
                }

                $query->latest();
            },
            relations: [
                'passenger:id,first_name,last_name,phone_number',
                'ridePost:id,driver_id,from_city_id,to_city_id,departure_at,post_type',
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
                'ridePost:id,driver_id,from_city_id,to_city_id,departure_at,post_type,price_per_seat',
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

            if (!in_array($booking->status, ['pending', 'accepted'])) {
                throw new ApiException('This booking can no longer be cancelled.', 422);
            }

            // If it was accepted, release the reserved seats back to the post.
            if ($booking->status === 'accepted') {
                $post = $this->ridePostRepository->findActiveForBooking($booking->ride_post_id);

                if ($post->post_type === 'shared') {
                    $this->ridePostRepository->update($post->id, [
                        'available_seats' => ($post->available_seats ?? 0) + $booking->seats_booked,
                        'status'          => 'active',
                    ]);
                } else {
                    $this->ridePostRepository->update($post->id, ['status' => 'active']);
                }
            }

            $this->repository->update($booking->id, ['status' => 'cancelled']);

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

            if ($post->post_type === 'shared') {
                if ($post->available_seats < $booking->seats_booked) {
                    throw new ApiException('Not enough seats left to accept this booking.', 422);
                }

                $newSeats = $post->available_seats - $booking->seats_booked;

                $this->ridePostRepository->update($post->id, [
                    'available_seats' => $newSeats,
                    'status'          => $newSeats <= 0 ? 'full' : $post->status,
                ]);
            } else {
                $this->ridePostRepository->update($post->id, ['status' => 'full']);
            }

            $this->repository->update($booking->id, ['status' => 'accepted']);

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
