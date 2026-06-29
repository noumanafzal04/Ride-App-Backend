<?php

namespace App\Jobs;

use App\Models\User;
use App\Services\Notification\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

// Fans an admin announcement out to a target audience — one DB notification +
// Reverb event + FCM push per user. Runs on the queue so the request returns fast.
class SendBroadcastNotification implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public string $title,
        public string $message,
        public string $type = 'announcement',
        public string $audience = 'all',   // all | user_type | city
        public ?string $userType = null,
        public ?int $cityId = null,
    ) {}

    public function handle(NotificationService $notifications): void
    {
        $this->audienceQuery()
            ->select('id')
            ->chunkById(300, function ($users) use ($notifications) {
                foreach ($users as $user) {
                    $notifications->push($user->id, $this->type, $this->title, $this->message, [
                        'broadcast' => true,
                    ]);
                }
            });
    }

    /** Build the recipient query from the chosen audience. Shared with the count check. */
    public function audienceQuery()
    {
        $q = User::query()->where('status', 'active');

        if ($this->audience === 'user_type' && $this->userType) {
            $q->where('user_type', $this->userType);
        } elseif ($this->audience === 'city' && $this->cityId) {
            $q->where('city_id', $this->cityId);
        }

        return $q;
    }
}
