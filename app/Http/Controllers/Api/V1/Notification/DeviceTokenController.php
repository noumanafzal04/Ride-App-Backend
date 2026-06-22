<?php

namespace App\Http\Controllers\Api\V1\Notification;

use App\Actions\Notification\DeviceTokenAction;
use App\Http\Controllers\Controller;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class DeviceTokenController extends Controller
{
    public function __construct(protected DeviceTokenAction $action) {}

    public function store(Request $request)
    {
        $data = $request->validate([
            'token'    => ['required', 'string', 'max:512'],
            'platform' => ['nullable', 'string', 'max:20'],
        ]);

        $this->action->register(auth()->id(), $data['token'], $data['platform'] ?? null);

        return ApiResponse::noContent('Device registered.');
    }

    public function destroy(Request $request)
    {
        $data = $request->validate([
            'token' => ['required', 'string', 'max:512'],
        ]);

        $this->action->remove(auth()->id(), $data['token']);

        return ApiResponse::noContent('Device removed.');
    }
}
