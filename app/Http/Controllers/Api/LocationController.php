<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\UserLocation;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    /**
     * Update location
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'accuracy' => 'nullable|numeric',
            'altitude' => 'nullable|numeric',
            'speed' => 'nullable|numeric',
            'heading' => 'nullable|numeric',
            'location_source' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'country' => 'nullable|string',
        ]);

        $user = Auth::user();

        // Update user's location
        UserLocation::updateUserLocation($user->id, $validated);

        return response()->json(['status' => 'Location updated'], 200);
    }

    /**
     * Get current location
     */
    public function getCurrentLocation(): JsonResponse
    {
        $user = Auth::user();
        $location = $user->currentLocation;

        return response()->json($location, $location ? 200 : 404);
    }

    /**
     * Get location history
     */
    public function getLocationHistory(int $hours = 24): JsonResponse
    {
        $user = Auth::user();
        $history = UserLocation::getLocationHistory($user->id, $hours);

        return response()->json($history, 200);
    }
}
