<?php

namespace App\Jobs;

use App\Services\NotificationService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessScheduledNotifications implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Execute the job.
     */
    public function handle(NotificationService $notificationService): void
    {
        try {
            $processedCount = $notificationService->processScheduledNotifications();
            Log::info("Scheduled notifications job completed", [
                'processed_count' => $processedCount
            ]);
        } catch (\Exception $e) {
            Log::error("Error processing scheduled notifications: " . $e->getMessage());
            throw $e;
        }
    }
}
