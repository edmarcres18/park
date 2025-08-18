<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ParkingSession;
use App\Models\SiteSetting;
use App\Observers\ParkingSessionObserver;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Support\Facades\URL;

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
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        ParkingSession::observe(ParkingSessionObserver::class);

        // Inject site settings into all views
        View::composer('*', function ($view) {
            try {
                $appName = SiteSetting::getValue('app_name', config('app.name'));
                $brandLogo = SiteSetting::getValue('brand_logo', null);
                $locationName = SiteSetting::getValue('location_name', null);
            } catch (\Throwable $e) {
                // During testing with in-memory DB, migrations may not have run yet
                $appName = config('app.name');
                $brandLogo = null;
                $locationName = null;
            }

            $siteSettings = (object) [
                'app_name' => $appName,
                'brand_logo' => $brandLogo,
                'location_name' => $locationName,
            ];
            $view->with('siteSettings', $siteSettings);
        });

        // Define API rate limiter used by 'throttle:api'
        RateLimiter::for('api', function (Request $request) {
            $userId = optional($request->user())->id;
            if ($userId) {
                return Limit::perMinute(60)->by('user:'.$userId);
            }
            return Limit::perMinute(30)->by($request->ip());
        });
    }
}
