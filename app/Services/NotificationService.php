<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class NotificationService
{
    /**
     * Create a new notification
     */
    public function createNotification(array $data): Notification
    {
        return Notification::create([
            'title' => $data['title'],
            'message' => $data['message'],
            'type' => $data['type'] ?? 'general',
            'data' => $data['data'] ?? null,
            'scheduled_at' => $data['scheduled_at'] ?? null,
            'target_audience' => $data['target_audience'] ?? 'all',
            'target_users' => $data['target_users'] ?? null,
            'priority' => $data['priority'] ?? 'normal',
            'created_by' => $data['created_by'],
        ]);
    }

    /**
     * Send notification immediately
     */
    public function sendNotification(Notification $notification): bool
    {
        try {
            $targetUsers = $notification->getTargetUsers();
            
            if ($targetUsers->isEmpty()) {
                Log::warning("No target users found for notification ID: {$notification->id}");
                return false;
            }

            $successCount = 0;
            $totalCount = $targetUsers->count();

            foreach ($targetUsers as $user) {
                if ($this->sendPushNotification($user, $notification)) {
                    $successCount++;
                }
            }

            // Mark as sent if at least 50% were successful
            if ($successCount >= ($totalCount * 0.5)) {
                $notification->markAsSent();
                Log::info("Notification sent successfully", [
                    'notification_id' => $notification->id,
                    'success_count' => $successCount,
                    'total_count' => $totalCount
                ]);
                return true;
            } else {
                $notification->markAsFailed();
                Log::error("Notification failed to send to majority of users", [
                    'notification_id' => $notification->id,
                    'success_count' => $successCount,
                    'total_count' => $totalCount
                ]);
                return false;
            }

        } catch (Exception $e) {
            Log::error("Error sending notification: " . $e->getMessage(), [
                'notification_id' => $notification->id,
                'error' => $e->getTraceAsString()
            ]);
            $notification->markAsFailed();
            return false;
        }
    }

    /**
     * Send push notification to a specific user
     */
    private function sendPushNotification(User $user, Notification $notification): bool
    {
        try {
            // Get FCM server key from config
            $serverKey = config('services.firebase.server_key');
            
            if (!$serverKey) {
                Log::error("Firebase server key not configured");
                return false;
            }

            // Get user's FCM token (you'll need to add this field to users table)
            $fcmToken = $user->fcm_token ?? null;
            
            if (!$fcmToken) {
                Log::warning("No FCM token found for user ID: {$user->id}");
                return false;
            }

            $payload = [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $notification->title,
                    'body' => $notification->message,
                    'sound' => 'default',
                    'badge' => 1,
                ],
                'data' => array_merge([
                    'notification_id' => $notification->id,
                    'type' => $notification->type,
                    'priority' => $notification->priority,
                    'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                ], $notification->data ?? [])
            ];

            $response = Http::withHeaders([
                'Authorization' => 'key=' . $serverKey,
                'Content-Type' => 'application/json',
            ])->post('https://fcm.googleapis.com/fcm/send', $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['success']) && $responseData['success'] > 0) {
                    return true;
                }
            }

            Log::warning("FCM request failed", [
                'user_id' => $user->id,
                'response' => $response->body(),
                'status' => $response->status()
            ]);
            
            return false;

        } catch (Exception $e) {
            Log::error("Error sending push notification to user {$user->id}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Process scheduled notifications
     */
    public function processScheduledNotifications(): int
    {
        $notifications = Notification::readyToSend()->get();
        $processedCount = 0;

        foreach ($notifications as $notification) {
            if ($this->sendNotification($notification)) {
                $processedCount++;
            }
        }

        Log::info("Processed {$processedCount} scheduled notifications");
        return $processedCount;
    }

    /**
     * Cancel a scheduled notification
     */
    public function cancelNotification(Notification $notification): bool
    {
        if ($notification->status === 'pending') {
            $notification->cancel();
            return true;
        }
        
        return false;
    }

    /**
     * Get notification statistics
     */
    public function getNotificationStats(): array
    {
        return [
            'total' => Notification::count(),
            'pending' => Notification::where('status', 'pending')->count(),
            'sent' => Notification::where('status', 'sent')->count(),
            'failed' => Notification::where('status', 'failed')->count(),
            'cancelled' => Notification::where('status', 'cancelled')->count(),
            'scheduled' => Notification::where('status', 'pending')
                                    ->whereNotNull('scheduled_at')
                                    ->where('scheduled_at', '>', now())
                                    ->count(),
        ];
    }

    /**
     * Send bulk notification to all users
     */
    public function sendBulkNotification(array $data): Notification
    {
        $notification = $this->createNotification(array_merge($data, [
            'target_audience' => 'all'
        ]));

        // Send immediately if not scheduled
        if (!$notification->scheduled_at || $notification->scheduled_at->isPast()) {
            $this->sendNotification($notification);
        }

        return $notification;
    }

    /**
     * Send notification to specific users
     */
    public function sendToSpecificUsers(array $userIds, array $data): Notification
    {
        $notification = $this->createNotification(array_merge($data, [
            'target_audience' => 'specific',
            'target_users' => $userIds
        ]));

        // Send immediately if not scheduled
        if (!$notification->scheduled_at || $notification->scheduled_at->isPast()) {
            $this->sendNotification($notification);
        }

        return $notification;
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(string $role, array $data): Notification
    {
        $targetAudience = $role === 'attendant' ? 'attendants' : 'customers';
        
        $notification = $this->createNotification(array_merge($data, [
            'target_audience' => $targetAudience
        ]));

        // Send immediately if not scheduled
        if (!$notification->scheduled_at || $notification->scheduled_at->isPast()) {
            $this->sendNotification($notification);
        }

        return $notification;
    }
}
