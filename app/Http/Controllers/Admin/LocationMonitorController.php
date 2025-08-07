<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\UserLocation;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class LocationMonitorController extends Controller
{
    /**
     * Show location monitoring dashboard
     */
    public function index()
    {
        $stats = $this->getLocationStats();
        return view('admin.location-monitor.index', compact('stats'));
    }

    /**
     * Get real-time location data for all users
     */
    public function getRealTimeLocations(): JsonResponse
    {
        $locations = UserLocation::with(['user' => function($query) {
            $query->select('id', 'name', 'email');
        }])
        ->where('is_active', true)
        ->where('location_timestamp', '>=', now()->subHours(1))
        ->orderBy('location_timestamp', 'desc')
        ->get()
        ->map(function($location) {
            return [
                'id' => $location->id,
                'user_id' => $location->user_id,
                'user_name' => $location->user->name,
                'user_email' => $location->user->email,
                'latitude' => floatval($location->latitude),
                'longitude' => floatval($location->longitude),
                'accuracy' => $location->accuracy,
                'accuracy_label' => $location->accuracy_label,
                'altitude' => $location->altitude,
                'speed' => $location->speed,
                'heading' => $location->heading,
                'location_source' => $location->location_source,
                'address' => $location->address,
                'city' => $location->city,
                'country' => $location->country,
                'formatted_coordinates' => $location->formatted_coordinates,
                'location_timestamp' => $location->location_timestamp,
                'time_since' => $location->time_since,
                'is_online' => $location->location_timestamp->gt(now()->subMinutes(5))
            ];
        });

        return response()->json($locations);
    }

    /**
     * Get location history for a specific user
     */
    public function getUserLocationHistory(User $user, Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        $history = UserLocation::getLocationHistory($user->id, $hours);
        
        return response()->json($history);
    }

    /**
     * Get users within radius
     */
    public function getUsersWithinRadius(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius' => 'required|numeric|min:0.1|max:100'
        ]);

        $users = UserLocation::getUsersWithinRadius(
            $validated['latitude'],
            $validated['longitude'],
            $validated['radius']
        );

        return response()->json($users);
    }

    /**
     * Get location statistics
     */
    public function getLocationStats(): array
    {
        $totalUsers = User::count();
        $usersWithLocation = User::whereHas('locations')->count();
        $onlineUsers = User::whereHas('currentLocation', function($query) {
            $query->where('location_timestamp', '>=', now()->subMinutes(5));
        })->count();
        
        $recentActivity = UserLocation::where('location_timestamp', '>=', now()->subHour())
                                    ->count();
        
        $locationSources = UserLocation::where('is_active', true)
                                     ->select('location_source', DB::raw('count(*) as count'))
                                     ->groupBy('location_source')
                                     ->get()
                                     ->pluck('count', 'location_source')
                                     ->toArray();

        return [
            'total_users' => $totalUsers,
            'users_with_location' => $usersWithLocation,
            'online_users' => $onlineUsers,
            'recent_activity' => $recentActivity,
            'location_sources' => $locationSources,
            'tracking_percentage' => $totalUsers > 0 ? round(($usersWithLocation / $totalUsers) * 100, 2) : 0
        ];
    }

    /**
     * Get location heatmap data
     */
    public function getHeatmapData(Request $request): JsonResponse
    {
        $hours = $request->get('hours', 24);
        
        $heatmapData = UserLocation::where('location_timestamp', '>=', now()->subHours($hours))
                                 ->select('latitude', 'longitude', DB::raw('count(*) as weight'))
                                 ->groupBy('latitude', 'longitude')
                                 ->get()
                                 ->map(function($point) {
                                     return [
                                         'lat' => floatval($point->latitude),
                                         'lng' => floatval($point->longitude),
                                         'weight' => $point->weight
                                     ];
                                 });

        return response()->json($heatmapData);
    }

    /**
     * Export location data
     */
    public function exportLocationData(Request $request)
    {
        $startDate = $request->get('start_date', now()->subDays(7));
        $endDate = $request->get('end_date', now());
        
        $locations = UserLocation::with('user')
                                ->whereBetween('location_timestamp', [$startDate, $endDate])
                                ->orderBy('location_timestamp', 'desc')
                                ->get();

        $filename = 'location_data_' . now()->format('Y-m-d_H-i-s') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function() use ($locations) {
            $file = fopen('php://output', 'w');
            
            // CSV headers
            fputcsv($file, [
                'User ID', 'User Name', 'Email', 'Latitude', 'Longitude', 
                'Accuracy', 'Altitude', 'Speed', 'Heading', 'Source', 
                'Address', 'City', 'Country', 'Timestamp'
            ]);
            
            foreach ($locations as $location) {
                fputcsv($file, [
                    $location->user_id,
                    $location->user->name,
                    $location->user->email,
                    $location->latitude,
                    $location->longitude,
                    $location->accuracy,
                    $location->altitude,
                    $location->speed,
                    $location->heading,
                    $location->location_source,
                    $location->address,
                    $location->city,
                    $location->country,
                    $location->location_timestamp->format('Y-m-d H:i:s')
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Clear old location data
     */
    public function clearOldLocationData(Request $request): JsonResponse
    {
        $days = $request->get('days', 30);
        
        $deleted = UserLocation::where('location_timestamp', '<', now()->subDays($days))
                              ->where('is_active', false)
                              ->delete();

        return response()->json([
            'message' => "Deleted {$deleted} old location records",
            'deleted_count' => $deleted
        ]);
    }
}
