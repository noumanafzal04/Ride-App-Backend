<?php

namespace App\Http\Controllers\Api\V1\Chat;

use App\Actions\Chat\ChatAction;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\Chat\SendMessageRequest;
use App\Http\Resources\Api\V1\Chat\ConversationResource;
use App\Http\Resources\Api\V1\Chat\MessageResource;
use App\Support\ApiResponse;

class ChatController extends Controller
{
    public function __construct(protected ChatAction $action) {}

    public function index()
    {
        $items = $this->action->inbox(auth()->id());

        return ConversationResource::collection($items)
            ->wrapWith('conversations')
            ->message('Your conversations.');
    }

    public function unreadCount()
    {
        return ApiResponse::success(
            ['unread_count' => $this->action->unreadCount(auth()->id())],
            'Unread count.'
        );
    }

    public function messages(int $id)
    {
        $items = $this->action->threadMessages(auth()->id(), $id);

        return MessageResource::collection($items)
            ->wrapWith('messages')
            ->message('Messages.');
    }

    public function send(SendMessageRequest $request, int $id)
    {
        $message = $this->action->send(auth()->id(), $id, $request->validated()['body']);

        return (new MessageResource($message))
            ->message('Message sent.')
            ->status(201);
    }

    public function read(int $id)
    {
        $this->action->markRead(auth()->id(), $id);

        return ApiResponse::noContent('Marked as read.');
    }

    /** Open (or look up) the conversation for a ride booking. */
    public function showForBooking(int $bookingId)
    {
        $conversation = $this->action->showForBooking(auth()->id(), $bookingId);

        return (new ConversationResource($conversation))
            ->message('Conversation.');
    }

    /** Open (or look up) the conversation for a service booking. */
    public function showForServiceBooking(int $serviceBookingId)
    {
        $conversation = $this->action->showForServiceBooking(auth()->id(), $serviceBookingId);

        return (new ConversationResource($conversation))
            ->message('Conversation.');
    }

    /** Open (or start) the buyer↔seller conversation for a car listing. */
    public function showForListing(int $listingId)
    {
        $conversation = $this->action->openForListing(auth()->id(), $listingId);

        return (new ConversationResource($conversation))
            ->message('Conversation.');
    }

    /** Open (or start) the customer↔owner conversation for a rental booking. */
    public function showForRentalBooking(int $bookingId)
    {
        $conversation = $this->action->openForRentalBooking(auth()->id(), $bookingId);

        return (new ConversationResource($conversation))
            ->message('Conversation.');
    }
}
