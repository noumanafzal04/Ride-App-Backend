<?php

namespace App\Services\Notification;

use App\Models\DeviceToken;
use Google\Auth\Credentials\ServiceAccountCredentials;
use Illuminate\Support\Facades\Http;

class FcmService
{
    protected ?array $sa = null;
    protected ?string $projectId = null;

    public function __construct()
    {
        $path = config('services.fcm.credentials');
        if ($path && is_file($path)) {
            $this->sa = json_decode(file_get_contents($path), true);
            $this->projectId = $this->sa['project_id'] ?? null;
        }
    }

    // True once the service-account JSON is present (Step 3).
    public function enabled(): bool
    {
        return $this->sa !== null && $this->projectId !== null;
    }

    // Best-effort push to every device a user has registered.
    public function sendToUser(int $userId, string $type, string $title, string $body, array $data = []): void
    {
        if (!$this->enabled()) {
            return;
        }

        $tokens = DeviceToken::where('user_id', $userId)->pluck('token', 'id');
        if ($tokens->isEmpty()) {
            return;
        }

        $access = $this->accessToken();
        if (!$access) {
            return;
        }

        // FCM data values must all be strings.
        $payloadData = collect(array_merge($data, ['type' => $type]))
            ->map(fn($v) => is_scalar($v) || $v === null ? (string) $v : json_encode($v))
            ->all();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        foreach ($tokens as $id => $token) {
            try {
                $resp = Http::withToken($access)->acceptJson()->post($url, [
                    'message' => [
                        'token'        => $token,
                        'notification' => ['title' => $title, 'body' => $body],
                        'data'         => $payloadData,
                        'android'      => ['priority' => 'HIGH', 'notification' => ['sound' => 'default', 'channel_id' => 'default']],
                        'apns'         => ['payload' => ['aps' => ['sound' => 'default']]],
                    ],
                ]);

                if ($resp->failed()) {
                    $status = $resp->json('error.status');
                    // Token is dead → prune so we stop trying.
                    if (in_array($status, ['NOT_FOUND', 'UNREGISTERED', 'INVALID_ARGUMENT'], true)) {
                        DeviceToken::where('id', $id)->delete();
                    }
                }
            } catch (\Throwable $e) {
                // push is best-effort — never break the calling flow
            }
        }
    }

    protected function accessToken(): ?string
    {
        try {
            $creds = new ServiceAccountCredentials('https://www.googleapis.com/auth/firebase.messaging', $this->sa);
            $token = $creds->fetchAuthToken();
            return $token['access_token'] ?? null;
        } catch (\Throwable $e) {
            return null;
        }
    }
}
