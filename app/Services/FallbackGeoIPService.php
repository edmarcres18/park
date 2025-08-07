<?php

namespace App\Services;

use Torann\GeoIP\Services\AbstractService;

class FallbackGeoIPService extends AbstractService
{
    /**
     * {@inheritdoc}
     */
    public function locate($ip)
    {
        $default = config('geoip.default_location');
        
        // For local IPs, return default location
        if ($this->isLocalIp($ip)) {
            return $this->hydrate([
                'ip' => $ip,
                'iso_code' => $default['iso_code'],
                'country' => $default['country'],
                'city' => $default['city'],
                'state' => $default['state'],
                'state_name' => $default['state_name'],
                'postal_code' => $default['postal_code'],
                'lat' => $default['lat'],
                'lon' => $default['lon'],
                'timezone' => $default['timezone'],
                'continent' => $default['continent'],
                'default' => true,
                'currency' => $default['currency'] ?? 'USD',
            ]);
        }

        // For external IPs, try to get basic info or return default
        return $this->hydrate([
            'ip' => $ip,
            'iso_code' => 'US',
            'country' => 'United States',
            'city' => 'Unknown',
            'state' => 'Unknown',
            'state_name' => 'Unknown',
            'postal_code' => '00000',
            'lat' => 0,
            'lon' => 0,
            'timezone' => 'UTC',
            'continent' => 'NA',
            'default' => false,
            'currency' => 'USD',
        ]);
    }

    /**
     * Check if IP is local/private
     */
    private function isLocalIp($ip)
    {
        return in_array($ip, ['127.0.0.1', '::1', 'localhost']) || 
               !filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
    }
}
