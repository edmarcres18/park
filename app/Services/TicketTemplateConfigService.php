<?php

namespace App\Services;

use App\Models\SiteSetting;
use Illuminate\Support\Facades\Storage;

class TicketTemplateConfigService
{
    const LOGO_KEY = 'ticket_logo';
    const LOCATION_ADDRESS_KEY = 'ticket_location_address';
    const GROUP = 'ticket';

    /**
     * Get the logo URL (public path)
     */
    public function getLogo(): ?string
    {
        $path = SiteSetting::getValue(self::LOGO_KEY);
        if ($path) {
            // If already a full URL, return as is
            if (str_starts_with($path, 'http')) {
                return $path;
            }
            // Otherwise, return as public asset
            return asset('uploads/' . ltrim($path, '/'));
        }
        return null;
    }

    /**
     * Get the location address
     */
    public function getLocationAddress(): ?string
    {
        return SiteSetting::getValue(self::LOCATION_ADDRESS_KEY);
    }

    /**
     * Set the logo path (after upload)
     */
    public function setLogo(string $path): void
    {
        SiteSetting::setValue(self::LOGO_KEY, $path, 'string', self::GROUP, 'Ticket logo image path');
    }

    /**
     * Set the location address
     */
    public function setLocationAddress(string $address): void
    {
        SiteSetting::setValue(self::LOCATION_ADDRESS_KEY, $address, 'string', self::GROUP, 'Ticket location address');
    }

    /**
     * Get both config values as array
     */
    public function getConfig(): array
    {
        return [
            'logo' => $this->getLogo(),
            'location_address' => $this->getLocationAddress(),
        ];
    }
}
