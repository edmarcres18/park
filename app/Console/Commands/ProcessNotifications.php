<?php

namespace App\Console\Commands;

use App\Jobs\ProcessScheduledNotifications;
use Illuminate\Console\Command;

class ProcessNotifications extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:process';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process scheduled notifications';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Processing scheduled notifications...');
        
        ProcessScheduledNotifications::dispatch();
        
        $this->info('Scheduled notifications job dispatched successfully.');
        
        return 0;
    }
}
