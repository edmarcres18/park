<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Login;
use Illuminate\Auth\Events\Logout;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Spatie\Activitylog\Models\Activity;
use Torann\GeoIP\Facades\GeoIP;

class LogAuthActivity
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(object $event): void
    {
        if ($event instanceof Login) {
            $this->logLogin($event);
        } elseif ($event instanceof Logout) {
            $this->logLogout($event);
        }
    }

    /**
     * Log user login event
     */
    private function logLogin(Login $event): void
    {
        $ip = request()->ip();
        $geoData = GeoIP::getLocation($ip);
        
        activity('authentication')
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $ip,
                'location' => [
                    'country' => $geoData->country ?? 'Unknown',
                    'city' => $geoData->city ?? 'Unknown',
                    'state' => $geoData->state_name ?? 'Unknown',
                ],
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ])
            ->log('User logged in');
    }

    /**
     * Log user logout event
     */
    private function logLogout(Logout $event): void
    {
        $ip = request()->ip();
        $geoData = GeoIP::getLocation($ip);
        
        activity('authentication')
            ->causedBy($event->user)
            ->withProperties([
                'ip' => $ip,
                'location' => [
                    'country' => $geoData->country ?? 'Unknown',
                    'city' => $geoData->city ?? 'Unknown',
                    'state' => $geoData->state_name ?? 'Unknown',
                ],
                'user_agent' => request()->userAgent(),
                'timestamp' => now(),
            ])
            ->log('User logged out');
    }
}
