<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CityController extends Controller
{
    public function search(Request $request): JsonResponse
    {
        $q = $request->input('q', '');

        if (strlen($q) < 3) {
            return response()->json([]);
        }

        $cities = City::where('name', 'like', $q . '%')
            ->select('id', 'name', 'province', 'lat', 'lon')
            ->orderBy('name')
            ->limit(8)
            ->get();

        return response()->json($cities);
    }
}
