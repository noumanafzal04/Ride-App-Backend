<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CityDistance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class DistanceController extends Controller
{
    public function calculate(Request $request): JsonResponse
    {
        $fromId = $request->input('from_id');
        $toId   = $request->input('to_id');

        if (!$fromId || !$toId) {
            return response()->json(['error' => 'from_id and to_id are required'], 422);
        }

        $distance = CityDistance::with(['fromCity', 'toCity'])
            ->where('from_city_id', $fromId)
            ->where('to_city_id',   $toId)
            ->first();

        if (!$distance) {
            return response()->json(['error' => 'Route not found'], 404);
        }

        return response()->json([
            'from'         => $distance->fromCity->name,
            'to'           => $distance->toCity->name,
            'distance_km'  => $distance->distance_km,
            'duration_min' => $distance->duration_min,
            'duration_hrs' => round($distance->duration_min / 60, 1),
        ]);
    }
}
