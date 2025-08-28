<?php

namespace App\Http\Controllers;

use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * Get all notifications with pagination
     */
    public function index(Request $request): JsonResponse
    {
        $query = Notification::with('creator')
                            ->orderBy('created_at', 'desc');

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Filter by type
        if ($request->has('type')) {
            $query->where('type', $request->type);
        }

        // Filter by priority
        if ($request->has('priority')) {
            $query->where('priority', $request->priority);
        }

        $notifications = $query->paginate($request->get('per_page', 15));

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Create a new notification
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'in:general,parking,system,alert',
            'scheduled_at' => 'nullable|date|after:now',
            'target_audience' => 'in:all,attendants,customers,specific',
            'target_users' => 'nullable|array',
            'target_users.*' => 'exists:users,id',
            'priority' => 'in:low,normal,high,urgent',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificationData = array_merge($request->all(), [
                'created_by' => Auth::id()
            ]);

            $notification = $this->notificationService->createNotification($notificationData);

            // Send immediately if not scheduled
            if (!$notification->scheduled_at || $notification->scheduled_at->isPast()) {
                $this->notificationService->sendNotification($notification);
            }

            return response()->json([
                'success' => true,
                'message' => 'Notification created successfully',
                'data' => $notification->load('creator')
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get a specific notification
     */
    public function show(Notification $notification): JsonResponse
    {
        return response()->json([
            'success' => true,
            'data' => $notification->load('creator')
        ]);
    }

    /**
     * Update a notification (only if pending)
     */
    public function update(Request $request, Notification $notification): JsonResponse
    {
        if ($notification->status !== 'pending') {
            return response()->json([
                'success' => false,
                'message' => 'Only pending notifications can be updated'
            ], 400);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'string|max:255',
            'message' => 'string',
            'type' => 'in:general,parking,system,alert',
            'scheduled_at' => 'nullable|date|after:now',
            'target_audience' => 'in:all,attendants,customers,specific',
            'target_users' => 'nullable|array',
            'target_users.*' => 'exists:users,id',
            'priority' => 'in:low,normal,high,urgent',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notification->update($request->all());

            return response()->json([
                'success' => true,
                'message' => 'Notification updated successfully',
                'data' => $notification->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Cancel a notification
     */
    public function cancel(Notification $notification): JsonResponse
    {
        if ($this->notificationService->cancelNotification($notification)) {
            return response()->json([
                'success' => true,
                'message' => 'Notification cancelled successfully'
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Cannot cancel this notification'
        ], 400);
    }

    /**
     * Send bulk notification to all users
     */
    public function sendBulk(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'type' => 'in:general,parking,system,alert',
            'scheduled_at' => 'nullable|date|after:now',
            'priority' => 'in:low,normal,high,urgent',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificationData = array_merge($request->all(), [
                'created_by' => Auth::id()
            ]);

            $notification = $this->notificationService->sendBulkNotification($notificationData);

            return response()->json([
                'success' => true,
                'message' => 'Bulk notification sent successfully',
                'data' => $notification->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send bulk notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to specific users
     */
    public function sendToUsers(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'type' => 'in:general,parking,system,alert',
            'scheduled_at' => 'nullable|date|after:now',
            'priority' => 'in:low,normal,high,urgent',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificationData = array_merge($request->except('user_ids'), [
                'created_by' => Auth::id()
            ]);

            $notification = $this->notificationService->sendToSpecificUsers(
                $request->user_ids,
                $notificationData
            );

            return response()->json([
                'success' => true,
                'message' => 'Notification sent to specific users successfully',
                'data' => $notification->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Send notification to users by role
     */
    public function sendToRole(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'role' => 'required|in:attendant,customer',
            'type' => 'in:general,parking,system,alert',
            'scheduled_at' => 'nullable|date|after:now',
            'priority' => 'in:low,normal,high,urgent',
            'data' => 'nullable|array'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $notificationData = array_merge($request->except('role'), [
                'created_by' => Auth::id()
            ]);

            $notification = $this->notificationService->sendToRole(
                $request->role,
                $notificationData
            );

            return response()->json([
                'success' => true,
                'message' => "Notification sent to {$request->role}s successfully",
                'data' => $notification->load('creator')
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to send notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get notification statistics
     */
    public function stats(): JsonResponse
    {
        $stats = $this->notificationService->getNotificationStats();

        return response()->json([
            'success' => true,
            'data' => $stats
        ]);
    }

    /**
     * Get scheduled notifications
     */
    public function scheduled(): JsonResponse
    {
        $notifications = Notification::where('status', 'pending')
                                   ->whereNotNull('scheduled_at')
                                   ->where('scheduled_at', '>', now())
                                   ->with('creator')
                                   ->orderBy('scheduled_at', 'asc')
                                   ->get();

        return response()->json([
            'success' => true,
            'data' => $notifications
        ]);
    }

    /**
     * Delete a notification
     */
    public function destroy(Notification $notification): JsonResponse
    {
        try {
            $notification->delete();

            return response()->json([
                'success' => true,
                'message' => 'Notification deleted successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to delete notification: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Update user FCM token
     */
    public function updateFcmToken(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'fcm_token' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed',
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $user = Auth::user();
            $user->update(['fcm_token' => $request->fcm_token]);

            return response()->json([
                'success' => true,
                'message' => 'FCM token updated successfully'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to update FCM token: ' . $e->getMessage()
            ], 500);
        }
    }
}
