<?php

namespace App\Actions\Ride;

use App\Actions\BaseAction\BaseAction;
use App\Exceptions\ApiException;
use App\Models\RideBooking;
use App\Repositories\Driver\RidePostRepository;
use App\Repositories\Ride\BookingRepository;
use Illuminate\Support\Facades\DB;

class BookingAction extends BaseAction
{
    public function __construct(
        BookingRepository $repository,
        protected RidePostRepository $ridePostRepository,
    ) {
        parent::__construct($repository, 'ride_booking');
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
            ],
        );
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
}
