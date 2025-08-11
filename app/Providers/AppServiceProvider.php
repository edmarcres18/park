<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ParkingSession;
use App\Models\SiteSetting;
use App\Observers\ParkingSessionObserver;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        ParkingSession::observe(ParkingSessionObserver::class);

        // Inject site settings into all views
        View::composer('*', function ($view) {
            try {
                $appName = SiteSetting::getValue('app_name', config('app.name'));
                $brandLogo = SiteSetting::getValue('brand_logo', null);
            } catch (\Throwable $e) {
                // During testing with in-memory DB, migrations may not have run yet
                $appName = config('app.name');
                $brandLogo = null;
            }

            $siteSettings = (object) [
                'app_name' => $appName,
                'brand_logo' => $brandLogo,
            ];
            $view->with('siteSettings', $siteSettings);
        });
    }
}
