<?php

namespace App\Http\Controllers\Api\V1;

use App\Actions\WorldAction;
use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\CitiesResource;
use Illuminate\Http\Request;

class WorldController extends Controller
{
    public function __construct(protected WorldAction $action) {}


    public function cities(Request $request)
    {
        $filters = $request->validate([
            'keywords' => ['nullable', 'string', 'max:255'],
        ]);
        $records = $this->action->list($filters);

        return CitiesResource::collection($records)
            ->wrapWith('cities')
            ->message(__("cities.all"));
    }
}
