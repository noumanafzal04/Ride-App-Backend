<?php

namespace App\Actions\Rental;

use App\Exceptions\ApiException;
use App\Models\RentalBooking;
use App\Models\RentalCar;
use App\Repositories\Rental\RentalBookingRepository;
use App\Repositories\Rental\RentalCarRepository;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

class RentalBookingAction
{
    public function __construct(
        protected RentalBookingRepository $repository,
        protected RentalCarRepository $cars,
        protected NotificationService $notifications,
    ) {}

    public function request(int $customerId, int $rentalId, array $data): RentalBooking
    {
        return DB::transaction(function () use ($customerId, $rentalId, $data) {
            $car = $this->cars->findActiveWithRelations($rentalId);
            if (!$car) throw new ApiException('This rental is not available.', 422);
            if ((int) $car->user_id === (int) $customerId) throw new ApiException('You cannot book your own rental.', 422);

            $start = Carbon::parse($data['start_date'])->startOfDay();
            $end   = Carbon::parse($data['end_date'])->startOfDay();
            if ($end->lt($start)) throw new ApiException('End date must be after the start date.', 422);

            $days = $start->diffInDays($end) + 1;
            if ($days < ($car->min_days ?? 1)) {
                throw new ApiException("Minimum rental is {$car->min_days} day(s).", 422);
            }

            $withDriver = match ($car->rental_type) {
                'with_driver' => true,
                'self_drive'  => false,
                default       => (bool) ($data['with_driver'] ?? true), // 'both'
            };

            $rate = $withDriver ? $car->price_per_day : ($car->price_per_day_self ?? $car->price_per_day);
            $total = $rate ? $rate * $days : null;
            $deposit = $withDriver ? null : $car->deposit;

            $booking = $this->repository->create([
                'rental_car_id'   => $car->id,
                'customer_id'     => $customerId,
                'owner_id'        => $car->user_id,
                'start_date'      => $start->toDateString(),
                'end_date'        => $end->toDateString(),
                'days'            => $days,
                'with_driver'     => $withDriver,
                'pickup_location' => $data['pickup_location'] ?? null,
                'total_amount'    => $total,
                'deposit'         => $deposit,
                'status'          => RentalBooking::STATUS_REQUESTED,
                'notes'           => $data['notes'] ?? null,
            ]);

            $this->notifications->push($car->user_id, 'rental_booking_requested', 'New rental request',
                "A rental request for your {$car->make} {$car->model} ({$days} day" . ($days > 1 ? 's' : '') . ').',
                ['rental_booking_id' => $booking->id]);

            return $this->repository->showWithRelations($booking->id);
        });
    }

    public function listForCustomer(int $customerId)
    {
        return $this->repository->paginatedForCustomer($customerId);
    }

    public function listForOwner(int $ownerId, ?string $status = null)
    {
        return $this->repository->paginatedForOwner($ownerId, $status);
    }

    public function cancel(int $customerId, int $id): RentalBooking
    {
        $booking = $this->repository->findOrFail($id);
        if ((int) $booking->customer_id !== (int) $customerId) throw new ApiException('You do not own this booking.', 403);
        if (!in_array($booking->status, [RentalBooking::STATUS_REQUESTED, RentalBooking::STATUS_CONFIRMED], true)) {
            throw new ApiException('This booking can no longer be cancelled.', 422);
        }
        $this->repository->update($id, ['status' => RentalBooking::STATUS_CANCELLED]);
        $this->notifications->push($booking->owner_id, 'rental_booking_cancelled', 'Rental cancelled',
            'A rental booking was cancelled by the customer.', ['rental_booking_id' => $id]);
        return $this->repository->showWithRelations($id);
    }

    // Owner transitions: accept | reject | start | complete
    public function ownerAction(int $ownerId, int $id, string $action): RentalBooking
    {
        $booking = $this->repository->findOrFail($id);
        if ((int) $booking->owner_id !== (int) $ownerId) throw new ApiException('You do not own this rental.', 403);

        [$from, $to, $type, $title, $msg] = match ($action) {
            'accept'   => [RentalBooking::STATUS_REQUESTED, RentalBooking::STATUS_CONFIRMED, 'rental_booking_confirmed', 'Rental confirmed', 'Your rental request was accepted.'],
            'reject'   => [RentalBooking::STATUS_REQUESTED, RentalBooking::STATUS_REJECTED, 'rental_booking_rejected', 'Rental declined', 'Your rental request was declined.'],
            'start'    => [RentalBooking::STATUS_CONFIRMED, RentalBooking::STATUS_ACTIVE, 'rental_booking_started', 'Rental started', 'Your rental has started.'],
            'complete' => [RentalBooking::STATUS_ACTIVE, RentalBooking::STATUS_COMPLETED, 'rental_booking_completed', 'Rental completed', 'Your rental is complete.'],
            default    => throw new ApiException('Invalid action.', 422),
        };

        if ($booking->status !== $from) throw new ApiException("Cannot {$action} a booking that is {$booking->status}.", 422);

        $this->repository->update($id, ['status' => $to]);
        $this->notifications->push($booking->customer_id, $type, $title, $msg, ['rental_booking_id' => $id]);

        return $this->repository->showWithRelations($id);
    }
}
