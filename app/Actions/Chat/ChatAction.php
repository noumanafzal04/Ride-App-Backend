<?php

namespace App\Actions\Chat;

use App\Actions\BaseAction\BaseAction;
use App\Events\MessageSent;
use App\Exceptions\ApiException;
use App\Models\Conversation;
use App\Models\Message;
use App\Repositories\Chat\ConversationRepository;
use App\Repositories\Chat\MessageRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ChatAction extends BaseAction
{
    public function __construct(
        ConversationRepository $repository,
        protected MessageRepository $messages,
    ) {
        parent::__construct($repository, 'conversation');
    }

    public function inbox(int $userId, ?int $limit = null)
    {
        return $this->repository->paginatedForUser($userId, $limit);
    }

    public function unreadCount(int $userId): int
    {
        return $this->repository->unreadTotalForUser($userId);
    }

    public function threadMessages(int $userId, int $conversationId, ?int $limit = null)
    {
        $this->participantOrFail($userId, $conversationId);
        return $this->messages->paginatedForConversation($conversationId, $limit);
    }

    /** Opening a conversation by its booking (from a ride/booking screen). */
    public function showForBooking(int $userId, int $bookingId): Conversation
    {
        $conversation = $this->repository->findForBooking($bookingId);

        if (!$conversation) {
            throw new ApiException('No conversation for this booking yet.', 404);
        }
        if (!$conversation->isParticipant($userId)) {
            throw new ApiException('You are not part of this conversation.', 403);
        }

        return $conversation->load([
            'driver:id,first_name,last_name',
            'rider:id,first_name,last_name',
            'booking:id,seats_booked,total_amount',
            'ridePost:id,from_city_id,to_city_id,price_per_seat,departure_at',
            'ridePost.fromCity:id,name',
            'ridePost.toCity:id,name',
        ]);
    }

    /** Opening a conversation by its service booking. */
    public function showForServiceBooking(int $userId, int $serviceBookingId): Conversation
    {
        $conversation = $this->repository->findForServiceBooking($serviceBookingId);

        if (!$conversation) {
            throw new ApiException('No conversation for this request yet.', 404);
        }
        if (!$conversation->isParticipant($userId)) {
            throw new ApiException('You are not part of this conversation.', 403);
        }

        return $conversation->load([
            'driver:id,first_name,last_name',
            'rider:id,first_name,last_name',
            'serviceBooking:id,category_id,provider_id',
            'serviceBooking.category:id,name,icon',
            'serviceBooking.provider:id,business_name',
        ]);
    }

    public function markRead(int $userId, int $conversationId): void
    {
        $conversation = $this->participantOrFail($userId, $conversationId);
        $this->messages->markReadForViewer($conversationId, $userId);
        $this->repository->resetUnreadFor($conversation, $userId);
    }

    public function send(int $userId, int $conversationId, string $body): Message
    {
        return DB::transaction(function () use ($userId, $conversationId, $body) {
            $conversation = $this->participantOrFail($userId, $conversationId);

            if ($conversation->status !== Conversation::STATUS_OPEN) {
                throw new ApiException('This conversation is closed.', 422);
            }

            $message = $this->messages->create([
                'conversation_id' => $conversationId,
                'sender_id'       => $userId,
                'body'            => $body,
            ]);

            $this->repository->bumpForMessage($conversation, $userId, Str::limit($body, 150));

            // Live to the thread + the recipient's private channel (badge/inbox).
            broadcast(new MessageSent($message, $conversation->otherUserId($userId)));

            return $message;
        });
    }

    protected function participantOrFail(int $userId, int $conversationId): Conversation
    {
        $conversation = $this->repository->findOrFail($conversationId);

        if (!$conversation->isParticipant($userId)) {
            throw new ApiException('You are not part of this conversation.', 403);
        }

        return $conversation;
    }
}
