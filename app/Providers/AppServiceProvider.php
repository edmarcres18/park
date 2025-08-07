<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Models\ParkingSession;
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
    }
}
