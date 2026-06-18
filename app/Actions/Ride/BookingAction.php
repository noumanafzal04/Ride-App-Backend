<?php

namespace App\Actions\Ride;

use App\Exceptions\ApiException;
use App\Models\RideBooking;
use App\Models\RidePost;
use App\Repositories\Ride\BookingRepository;
use Illuminate\Support\Facades\DB;

class BookingAction
{
    public function __construct(protected BookingRepository $repository) {}

    /**
     * Passenger books seats on a ride post.
     */
    public function book(int $passengerId, RidePost $ridePost, array $data): RideBooking
    {
        return DB::transaction(function () use ($passengerId, $ridePost, $data) {

            if ($ridePost->driver_id === $passengerId) {
                throw new ApiException('You cannot book your own ride.', 422);
            }

            if ($ridePost->status !== 'active') {
                throw new ApiException('This ride is no longer available.', 422);
            }

            $seats = (int) $data['seats'];

            // Shared posts track seats; private books the whole vehicle.
            if ($ridePost->post_type === 'shared'
                && ($ridePost->available_seats === null || $seats > $ridePost->available_seats)) {
                throw new ApiException('Not enough seats available.', 422);
            }

            // One booking per passenger per post (DB-unique).
            $exists = RideBooking::where('ride_post_id', $ridePost->id)
                ->where('passenger_id', $passengerId)
                ->exists();

            if ($exists) {
                throw new ApiException('You already have a booking request for this ride.', 422);
            }

            return $this->repository->create([
                'ride_post_id'   => $ridePost->id,
                'passenger_id'   => $passengerId,
                'seats_booked'   => $seats,
                'price_per_seat' => $ridePost->price_per_seat,
                'total_amount'   => $seats * $ridePost->price_per_seat,
                'note'           => $data['note'] ?? null,
                'status'         => 'pending',
            ]);
        });
    }

    /**
     * Bookings made on the authenticated driver's ride posts.
     */
    public function driverBookings(int $driverId, array $filters = [])
    {
        return $this->repository->paginatedList(
            callback: function ($query) use ($driverId, $filters) {
                $query->whereHas('ridePost', fn($q) => $q->where('driver_id', $driverId));

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

    /**
     * Driver accepts a pending booking → reserve the seats.
     */
    public function accept(int $driverId, RideBooking $booking): RideBooking
    {
        return DB::transaction(function () use ($driverId, $booking) {

            $this->guardOwner($driverId, $booking);

            if ($booking->status !== 'pending') {
                throw new ApiException('Only pending bookings can be accepted.', 422);
            }

            $post = RidePost::whereKey($booking->ride_post_id)->lockForUpdate()->first();

            if ($post->post_type === 'shared') {
                if ($post->available_seats < $booking->seats_booked) {
                    throw new ApiException('Not enough seats left to accept this booking.', 422);
                }
                $post->available_seats -= $booking->seats_booked;
                if ($post->available_seats <= 0) {
                    $post->status = 'full';
                }
            } else {
                // Private — the whole vehicle is now booked.
                $post->status = 'full';
            }
            $post->save();

            $booking->status = 'accepted';
            $booking->save();

            return $booking;
        });
    }

    /**
     * Driver rejects a pending booking.
     */
    public function reject(int $driverId, RideBooking $booking): RideBooking
    {
        $this->guardOwner($driverId, $booking);

        if ($booking->status !== 'pending') {
            throw new ApiException('Only pending bookings can be rejected.', 422);
        }

        $booking->status = 'rejected';
        $booking->save();

        return $booking;
    }

    protected function guardOwner(int $driverId, RideBooking $booking): void
    {
        if ($booking->ridePost?->driver_id !== $driverId) {
            throw new ApiException('You do not own this booking.', 403);
        }
    }
}
