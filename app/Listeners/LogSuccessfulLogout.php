<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Http\Request;
use Torann\GeoIP\Facades\GeoIP;

class LogSuccessfulLogout
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct(Request $request)
    {
        $this->request = $request;
    }

    /**
     * Handle the event.
     *
     * @param  Logout  $event
     * @return void
     */
    public function handle(Logout $event)
    {
        $ip = $this->request->ip();
        $geoData = GeoIP::getLocation($ip);
        
        // Try to get GPS coordinates from session (if available from login)
        $storedLocation = session('user_gps_location');
        
        // Build location data, using stored GPS data if available
        $locationData = [
            'country' => $geoData->country ?? 'Unknown',
            'city' => $geoData->city ?? 'Unknown',
            'state' => $geoData->state_name ?? 'Unknown',
            'source' => 'ip',
        ];
        
        // If stored GPS coordinates are available, use them
        if ($storedLocation && isset($storedLocation['latitude']) && isset($storedLocation['longitude'])) {
            $locationData = array_merge($locationData, [
                'latitude' => (float) $storedLocation['latitude'],
                'longitude' => (float) $storedLocation['longitude'],
                'accuracy' => $storedLocation['accuracy'] ?? null,
                'source' => 'gps',
                'is_precise' => isset($storedLocation['accuracy']) ? (float) $storedLocation['accuracy'] <= 100 : false,
            ]);
        }
        
        activity('auth')
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $ip,
                'location' => $locationData,
                'user_agent' => $this->request->header('User-Agent'),
                'timestamp' => now(),
                'has_gps_location' => isset($locationData['latitude']),
            ])
            ->log('User logged out successfully');
            
        // Clear the stored location after logout
        session()->forget('user_gps_location');
    }
}
