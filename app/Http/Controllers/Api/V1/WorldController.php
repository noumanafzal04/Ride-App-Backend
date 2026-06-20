<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\WorldAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CitiesResource;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class WorldController extends Controller
{
    public function __construct(protected WorldAction $action) {}


    public function cities(Request $request)
    {
        $filters = $request->validate([
            'keywords' => ['nullable', 'string', 'max:255'],
        ]);

        // Cities change rarely but this is hit on every filter/onboarding screen.
        // Cache per keyword set for 24h (clear with `php artisan cache:clear` after seeding).
        $cacheKey = 'cities:' . md5(json_encode($filters));
        $records = Cache::remember($cacheKey, now()->addHours(24), fn() => $this->action->list($filters));

        return CitiesResource::collection($records)
            ->wrapWith('cities')
            ->message(__("cities.all"));
    }
}
