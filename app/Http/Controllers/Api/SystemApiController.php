<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SiteSetting;
use Illuminate\Http\JsonResponse;

class SystemApiController extends Controller
{
    /**
     * Return public system/site settings needed by clients.
     */
    public function settings(): JsonResponse
    {
        try {
            $appName = SiteSetting::getValue('app_name', config('app.name'));
            $brandLogo = SiteSetting::getValue('brand_logo', null);
            $locationName = SiteSetting::getValue('location_name', null);
        } catch (\Throwable $e) {
            // In environments where the settings table may not exist yet
            $appName = config('app.name');
            $brandLogo = null;
            $locationName = null;
        }

        return response()->json([
            'status' => 'success',
            'data' => [
                'app_name' => $appName,
                'brand_logo' => $brandLogo,
                'location_name' => $locationName,
            ],
        ]);
    }
}


