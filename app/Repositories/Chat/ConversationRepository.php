<?php

namespace App\Repositories\Chat;

use App\Models\Conversation;
use App\Repositories\BaseRepository;
use Illuminate\Support\Facades\DB;

class ConversationRepository extends BaseRepository
{
    public function __construct()
    {
        $this->model = new Conversation();
    }

    /** A user's conversations (newest activity first) with the other party + route. */
    public function paginatedForUser(int $userId, ?int $limit = null)
    {
        return $this->paginatedList(
            callback: fn($q) => $q
                ->where('driver_id', $userId)
                ->orWhere('rider_id', $userId)
                ->orderByDesc('last_message_at')
                ->orderByDesc('id'),
            relations: [
                'driver:id,first_name,last_name',
                'rider:id,first_name,last_name',
                'booking:id,seats_booked,total_amount',
                'ridePost:id,from_city_id,to_city_id,price_per_seat,departure_at',
                'ridePost.fromCity:id,name',
                'ridePost.toCity:id,name',
                'serviceBooking:id,category_id,provider_id',
                'serviceBooking.category:id,name,icon',
                'serviceBooking.provider:id,business_name',
            ],
            limit: $limit,
        );
    }

    public function findForBooking(int $bookingId): ?Conversation
    {
        return $this->findOne(callback: fn($q) => $q->where('booking_id', $bookingId));
    }

    public function findForServiceBooking(int $serviceBookingId): ?Conversation
    {
        return $this->findOne(callback: fn($q) => $q->where('service_booking_id', $serviceBookingId));
    }

    // Find (or create) the buyer↔seller conversation for a listing.
    public function findOrCreateForListing(int $buyerId, int $sellerId, int $listingId): Conversation
    {
        $existing = $this->findOne(callback: fn($q) => $q
            ->where('car_listing_id', $listingId)
            ->where(fn($w) => $w
                ->where(fn($a) => $a->where('driver_id', $sellerId)->where('rider_id', $buyerId))
                ->orWhere(fn($b) => $b->where('driver_id', $buyerId)->where('rider_id', $sellerId))));

        if ($existing) {
            return $existing;
        }

        return $this->create([
            'type'           => 'listing',
            'car_listing_id' => $listingId,
            'driver_id'      => $sellerId,   // seller occupies the "driver" participant slot
            'rider_id'       => $buyerId,    // buyer occupies the "rider" participant slot
            'status'         => Conversation::STATUS_OPEN,
        ]);
    }

    public function closeByServiceBooking(int $serviceBookingId): void
    {
        $this->model->newQuery()
            ->where('service_booking_id', $serviceBookingId)
            ->where('status', Conversation::STATUS_OPEN)
            ->update(['status' => Conversation::STATUS_CLOSED, 'closed_at' => now()]);
    }

    /** Total unread across all of the user's conversations (single query). */
    public function unreadTotalForUser(int $userId): int
    {
        return (int) $this->model->newQuery()
            ->where('driver_id', $userId)->sum('driver_unread')
            + (int) $this->model->newQuery()
            ->where('rider_id', $userId)->sum('rider_unread');
    }

    /** Bump activity + the recipient's unread counter atomically. */
    public function bumpForMessage(Conversation $conversation, int $senderId, string $preview): void
    {
        $recipientCol = $senderId === $conversation->driver_id ? 'rider_unread' : 'driver_unread';

        $this->model->newQuery()->where('id', $conversation->id)->update([
            'last_message_preview' => $preview,
            'last_message_at'      => now(),
            $recipientCol          => DB::raw($recipientCol . ' + 1'),
        ]);
    }

    public function resetUnreadFor(Conversation $conversation, int $userId): void
    {
        $col = $userId === $conversation->driver_id ? 'driver_unread' : 'rider_unread';
        $this->update($conversation->id, [$col => 0]);
    }

    public function closeByRidePost(int $ridePostId): void
    {
        $this->model->newQuery()
            ->where('ride_post_id', $ridePostId)
            ->where('status', Conversation::STATUS_OPEN)
            ->update(['status' => Conversation::STATUS_CLOSED, 'closed_at' => now()]);
    }

    public function closeByBooking(int $bookingId): void
    {
        $this->model->newQuery()
            ->where('booking_id', $bookingId)
            ->where('status', Conversation::STATUS_OPEN)
            ->update(['status' => Conversation::STATUS_CLOSED, 'closed_at' => now()]);
    }

    /** IDs of conversations closed before the cutoff (for purge). */
    public function closedBefore(string $cutoff): array
    {
        return $this->model->newQuery()
            ->where('status', Conversation::STATUS_CLOSED)
            ->whereNotNull('closed_at')
            ->where('closed_at', '<', $cutoff)
            ->pluck('id')->all();
    }
}
