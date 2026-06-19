<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Actions\Notification\NotificationAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\Notification\NotificationResource;
use App\Support\ApiResponse;

class NotificationController extends Controller
{
    public $resourceName = 'notification';

    public function __construct(protected NotificationAction $action) {}

    public function index()
    {
        $items = $this->action->listForUser(auth()->id());

        return NotificationResource::collection($items)
            ->wrapWith('notifications')
            ->message(__("{$this->resourceName}.all"));
    }

    public function unreadCount()
    {
        return ApiResponse::success(
            ['unread_count' => $this->action->unreadCount(auth()->id())],
            __("{$this->resourceName}.all")
        );
    }

    public function markRead(int $id)
    {
        $this->action->markRead(auth()->id(), $id);

        return ApiResponse::noContent(__("{$this->resourceName}.read"));
    }

    public function markAllRead()
    {
        $this->action->markAllRead(auth()->id());

        return ApiResponse::noContent(__("{$this->resourceName}.read_all"));
    }
}
