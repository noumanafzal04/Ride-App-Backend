<?php

namespace App\Services\Chat;

use App\Models\Conversation;
use App\Models\RideBooking;
use App\Models\ServiceBooking;
use App\Repositories\Chat\ConversationRepository;
use Throwable;

/**
 * Lifecycle hooks for conversations, called from the booking/ride flow.
 * Fire-and-forget — a chat failure must never break booking/ride logic.
 */
class ChatService
{
    public function __construct(protected ConversationRepository $conversations) {}

    /** Open (or reuse) the conversation for an accepted booking. */
    public function openForBooking(RideBooking $booking): void
    {
        try {
            $driverId = $booking->ridePost?->driver_id;
            if (!$driverId) {
                return;
            }

            $this->conversations->updateOrCreate(
                ['booking_id' => $booking->id],
                [
                    'ride_post_id' => $booking->ride_post_id,
                    'driver_id'    => $driverId,
                    'rider_id'     => $booking->passenger_id,
                    'status'       => Conversation::STATUS_OPEN,
                    'closed_at'    => null,
                ],
            );
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Close all conversations for a ride (ride completed/cancelled). */
    public function closeForRidePost(int $ridePostId): void
    {
        try {
            $this->conversations->closeByRidePost($ridePostId);
        } catch (Throwable $e) {
            report($e);
        }
    }

    /** Close the conversation for a single booking (rider cancelled). */
    public function closeForBooking(int $bookingId): void
    {
        try {
            $this->conversations->closeByBooking($bookingId);
        } catch (Throwable $e) {
            report($e);
        }
    }

    // ── Service chats ──

    /** Open (or reuse) the conversation for an accepted service booking. */
    public function openForServiceBooking(ServiceBooking $booking, int $providerUserId): void
    {
        try {
            $this->conversations->updateOrCreate(
                ['service_booking_id' => $booking->id],
                [
                    'type'      => Conversation::TYPE_SERVICE,
                    'driver_id' => $providerUserId,        // provider = "party A"
                    'rider_id'  => $booking->customer_id,  // customer = "party B"
                    'status'    => Conversation::STATUS_OPEN,
                    'closed_at' => null,
                ],
            );
        } catch (Throwable $e) {
            report($e);
        }
    }

    public function closeForServiceBooking(int $serviceBookingId): void
    {
        try {
            $this->conversations->closeByServiceBooking($serviceBookingId);
        } catch (Throwable $e) {
            report($e);
        }
    }
}
