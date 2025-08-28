<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Console\Commands\ProcessNotifications;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Register the ProcessNotifications command
Artisan::command('notifications:process', function () {
    $this->call(ProcessNotifications::class);
})->purpose('Process scheduled notifications');
