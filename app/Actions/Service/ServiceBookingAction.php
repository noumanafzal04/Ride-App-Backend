<?php

namespace App\Actions\Service;

use App\Exceptions\ApiException;
use App\Models\ServiceBooking;
use App\Models\ServiceProvider;
use App\Repositories\Ride\RatingRepository;
use App\Repositories\Service\ServiceBookingRepository;
use App\Repositories\Service\ServiceProviderRepository;
use App\Services\Chat\ChatService;
use App\Services\Notification\NotificationService;
use Illuminate\Support\Facades\DB;

class ServiceBookingAction
{
    public function __construct(
        protected ServiceBookingRepository $repository,
        protected ServiceProviderRepository $providers,
        protected NotificationService $notifications,
        protected RatingRepository $ratings,
        protected ChatService $chat,
    ) {}

    // ─── Customer ─────────────────────────────────────────────

    public function request(int $customerId, int $providerId, array $data): ServiceBooking
    {
        return DB::transaction(function () use ($customerId, $providerId, $data) {
            $provider = $this->providers->approvedById($providerId);
            if (!$provider) {
                throw new ApiException('This provider is not available.', 422);
            }

            // A provider cannot book their own service.
            if ((int) $provider->user_id === (int) $customerId) {
                throw new ApiException('You cannot book your own service.', 422);
            }

            $booking = $this->repository->create([
                'customer_id'   => $customerId,
                'provider_id'   => $providerId,
                'category_id'   => $data['category_id'] ?? null,
                'scheduled_at'  => $data['scheduled_at'] ?? null,
                'location_type' => $data['location_type'] ?? 'at_shop',
                'address'       => $data['address'] ?? null,
                'car_info'      => $data['car_info'] ?? null,
                'notes'         => $data['notes'] ?? null,
                'status'        => ServiceBooking::STATUS_REQUESTED,
            ]);

            $this->notifications->push(
                $provider->user_id,
                'service_booking_requested',
                'New service request',
                'You have a new service request. Review and respond.',
                ['service_booking_id' => $booking->id],
            );

            return $this->repository->showWithRelations($booking->id);
        });
    }

    public function listForCustomer(int $customerId)
    {
        return $this->repository->paginatedForCustomer($customerId);
    }

    public function cancel(int $customerId, int $bookingId): ServiceBooking
    {
        return DB::transaction(function () use ($customerId, $bookingId) {
            $booking = $this->repository->findOrFail($bookingId);

            if ($booking->customer_id !== $customerId) {
                throw new ApiException('You do not own this request.', 403);
            }
            if (!in_array($booking->status, [ServiceBooking::STATUS_REQUESTED, ServiceBooking::STATUS_ACCEPTED], true)) {
                throw new ApiException('This request can no longer be cancelled.', 422);
            }

            $this->repository->update($bookingId, ['status' => ServiceBooking::STATUS_CANCELLED]);
            $this->chat->closeForServiceBooking($bookingId);
            $provider = $this->providers->findOrFail($booking->provider_id);
            $this->notifications->push(
                $provider->user_id,
                'service_booking_cancelled',
                'Request cancelled',
                'A customer cancelled their service request.',
                ['service_booking_id' => $booking->id],
            );

            return $this->repository->showWithRelations($bookingId);
        });
    }

    public function rate(int $customerId, int $bookingId, array $data): ServiceBooking
    {
        return DB::transaction(function () use ($customerId, $bookingId, $data) {
            $booking = $this->repository->showWithRelations($bookingId);

            if ($booking->customer_id !== $customerId) {
                throw new ApiException('You do not own this request.', 403);
            }
            if ($booking->status !== ServiceBooking::STATUS_COMPLETED) {
                throw new ApiException('You can review only after the service is completed.', 422);
            }

            $already = $this->ratings->findOne(callback: fn($q) => $q
                ->where('rateable_type', ServiceBooking::class)
                ->where('rateable_id', $booking->id)
                ->where('from_user_id', $customerId));
            if ($already) {
                throw new ApiException('You have already reviewed this service.', 422);
            }

            $providerUserId = $booking->provider?->user_id;

            $this->ratings->create([
                'type'          => 'service',
                'rateable_type' => ServiceBooking::class,
                'rateable_id'   => $booking->id,
                'from_user_id'  => $customerId,
                'to_user_id'    => $providerUserId,
                'rated_as'      => 'provider',
                'rating'        => (int) $data['rating'],
                'review'        => $data['review'] ?? null,
            ]);

            if ($providerUserId) {
                $avg = $this->ratings->avgForUser($providerUserId, 'service');
                $this->providers->setRatingAvg($booking->provider_id, $avg);
            }

            return $this->repository->showWithRelations($bookingId);
        });
    }

    // ─── Provider ─────────────────────────────────────────────

    public function providerBookings(int $userId, ?string $status = null)
    {
        $provider = $this->providerOrFail($userId);
        return $this->repository->paginatedForProvider($provider->id, $status);
    }

    public function accept(int $userId, int $bookingId, ?float $price = null): ServiceBooking
    {
        $booking = $this->transition($userId, $bookingId, ServiceBooking::STATUS_REQUESTED, ServiceBooking::STATUS_ACCEPTED, [
            'notifyType'    => 'service_booking_accepted',
            'notifyTitle'   => 'Request accepted',
            'notifyMessage' => 'Your service request was accepted.',
            'extra'         => $price !== null ? ['price' => $price] : [],
        ]);

        // Confirmed pair → open their chat ($userId is the provider's user).
        $this->chat->openForServiceBooking($booking, $userId);

        return $booking;
    }

    public function reject(int $userId, int $bookingId): ServiceBooking
    {
        $booking = $this->transition($userId, $bookingId, ServiceBooking::STATUS_REQUESTED, ServiceBooking::STATUS_REJECTED, [
            'notifyType'    => 'service_booking_rejected',
            'notifyTitle'   => 'Request declined',
            'notifyMessage' => 'Your service request was declined.',
        ]);
        $this->chat->closeForServiceBooking($bookingId);
        return $booking;
    }

    public function start(int $userId, int $bookingId): ServiceBooking
    {
        return $this->transition($userId, $bookingId, ServiceBooking::STATUS_ACCEPTED, ServiceBooking::STATUS_IN_PROGRESS, [
            'notifyType'    => 'service_booking_started',
            'notifyTitle'   => 'Service started',
            'notifyMessage' => 'Your service is now in progress.',
        ]);
    }

    public function complete(int $userId, int $bookingId): ServiceBooking
    {
        $booking = $this->transition($userId, $bookingId, ServiceBooking::STATUS_IN_PROGRESS, ServiceBooking::STATUS_COMPLETED, [
            'notifyType'    => 'service_booking_completed',
            'notifyTitle'   => 'Service completed',
            'notifyMessage' => 'Your service is complete. Leave a review!',
            'extra'         => ['completed_at' => now()],
        ]);

        $this->providers->incrementJobs($booking->provider_id);
        $this->chat->closeForServiceBooking($bookingId);

        return $booking;
    }

    // ─── Helpers ──────────────────────────────────────────────

    protected function providerOrFail(int $userId): ServiceProvider
    {
        $provider = $this->providers->forUser($userId);
        if (!$provider) {
            throw new ApiException('You are not a registered service provider.', 403);
        }
        return $provider;
    }

    /**
     * Provider-side status change with guard + customer notification.
     */
    protected function transition(int $userId, int $bookingId, string $from, string $to, array $opts): ServiceBooking
    {
        return DB::transaction(function () use ($userId, $bookingId, $from, $to, $opts) {
            $provider = $this->providerOrFail($userId);
            $booking  = $this->repository->findOrFail($bookingId);

            if ($booking->provider_id !== $provider->id) {
                throw new ApiException('This request is not assigned to you.', 403);
            }
            if ($booking->status !== $from) {
                throw new ApiException('This action is not allowed for the current status.', 422);
            }

            $this->repository->update($bookingId, array_merge(['status' => $to], $opts['extra'] ?? []));

            $this->notifications->push(
                $booking->customer_id,
                $opts['notifyType'],
                $opts['notifyTitle'],
                $opts['notifyMessage'],
                ['service_booking_id' => $booking->id],
            );

            return $this->repository->showWithRelations($bookingId);
        });
    }
}
