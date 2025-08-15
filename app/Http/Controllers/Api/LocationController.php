<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LocationController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth:sanctum', 'role:attendant']);
    }

    /**
     * Update current attendant location.
     */
    public function updateLocation(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'accuracy' => ['nullable', 'numeric', 'min:0'],
            'altitude' => ['nullable', 'numeric'],
            'speed' => ['nullable', 'numeric'],
            'heading' => ['nullable', 'numeric'],
            'location_source' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:100'],
            'country' => ['nullable', 'string', 'max:100'],
            'session_id' => ['nullable', 'integer'],
        ]);

        $location = UserLocation::updateUserLocation(Auth::id(), $validated);

        return response()->json([
            'status' => 'success',
            'message' => 'Location updated',
            'data' => [
                'id' => $location->id,
                'coordinates' => $location->formatted_coordinates,
                'accuracy_label' => $location->accuracy_label,
                'location_timestamp' => $location->location_timestamp?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get current location for authenticated attendant.
     */
    public function getCurrentLocation(): JsonResponse
    {
        $location = UserLocation::getCurrentLocation(Auth::id());

        if (!$location) {
            return response()->json([
                'status' => 'success',
                'data' => null,
            ]);
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'id' => $location->id,
                'latitude' => (float) $location->latitude,
                'longitude' => (float) $location->longitude,
                'accuracy' => $location->accuracy !== null ? (float) $location->accuracy : null,
                'address' => $location->address,
                'city' => $location->city,
                'country' => $location->country,
                'coordinates' => $location->formatted_coordinates,
                'accuracy_label' => $location->accuracy_label,
                'location_timestamp' => $location->location_timestamp?->toDateTimeString(),
            ],
        ]);
    }

    /**
     * Get location history for authenticated attendant for last X hours.
     */
    public function getLocationHistory(int $hours = 24): JsonResponse
    {
        $hours = max(1, min(168, $hours)); // Clamp between 1 hour and 7 days
        $history = UserLocation::getLocationHistory(Auth::id(), $hours)
            ->map(function (UserLocation $loc) {
                return [
                    'id' => $loc->id,
                    'latitude' => (float) $loc->latitude,
                    'longitude' => (float) $loc->longitude,
                    'accuracy' => $loc->accuracy !== null ? (float) $loc->accuracy : null,
                    'address' => $loc->address,
                    'city' => $loc->city,
                    'country' => $loc->country,
                    'is_active' => (bool) $loc->is_active,
                    'location_timestamp' => $loc->location_timestamp?->toDateTimeString(),
                ];
            });

        return response()->json([
            'status' => 'success',
            'data' => $history,
            'total' => $history->count(),
        ]);
    }
}


