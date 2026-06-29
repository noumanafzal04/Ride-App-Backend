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

    // Resolve the user's GPS coordinate to the nearest known city.
    public function nearestCity(Request $request)
    {
        $data = $request->validate([
            'lat' => ['required', 'numeric', 'between:-90,90'],
            'lng' => ['required', 'numeric', 'between:-180,180'],
        ]);

        $city = $this->action->nearest((float) $data['lat'], (float) $data['lng']);

        // Remember the user's city for targeted admin broadcasts.
        if ($city && ($user = $request->user()) && (int) $user->city_id !== (int) $city->id) {
            $user->forceFill(['city_id' => $city->id])->save();
        }

        return (new CitiesResource($city))->wrapWith('city');
    }
}
