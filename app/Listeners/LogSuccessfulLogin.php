<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Http\Request;
use Spatie\Activitylog\Facades\Log;
use Torann\GeoIP\Facades\GeoIP;

class LogSuccessfulLogin
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
     * @param  Login  $event
     * @return void
     */
    public function handle(Login $event)
    {
        $ip = $this->request->ip();
        $geoData = GeoIP::getLocation($ip);
        
        // Check if GPS coordinates were provided by the browser
        $latitude = $this->request->input('latitude');
        $longitude = $this->request->input('longitude');
        $locationAccuracy = $this->request->input('location_accuracy');
        
        // Build location data, prioritizing GPS over IP-based location
        $locationData = [
            'country' => $geoData->country ?? 'Unknown',
            'city' => $geoData->city ?? 'Unknown',
            'state' => $geoData->state_name ?? 'Unknown',
            'source' => 'ip', // Default to IP-based location
        ];
        
        // If GPS coordinates are available, add them and mark as GPS source
        if ($latitude && $longitude) {
            $gpsData = [
                'latitude' => (float) $latitude,
                'longitude' => (float) $longitude,
                'accuracy' => $locationAccuracy ? (float) $locationAccuracy : null,
                'source' => 'gps',
                'is_precise' => $locationAccuracy ? (float) $locationAccuracy <= 100 : false,
            ];
            
            $locationData = array_merge($locationData, $gpsData);
            
            // Store GPS coordinates in session for use during logout
            session(['user_gps_location' => $gpsData]);
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
            ->log('User logged in successfully');
    }
}
