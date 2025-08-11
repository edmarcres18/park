<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NotificationController extends Controller
{
    public function index()
    {
        $notifications = Auth::user()->notifications()->latest()->paginate(20);
        return view('admin.notifications.index', compact('notifications'));
    }

    public function markAsRead($id)
    {
        $notification = Auth::user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return response()->json(['success' => true]);
    }
    public function markAllAsRead()
    {
        Auth::user()->unreadNotifications->markAsRead();

        return response()->json(['success' => true]);
    }

    public function getUnreadNotifications()
    {
        $user = Auth::user();
        $notifications = $user->unreadNotifications()->latest()->take(20)->get()->map(function ($n) {
            return [
                'id' => $n->id,
                'title' => $n->data['title'] ?? ($n->data['name'] ?? 'Notification'),
                'message' => $n->data['message'] ?? null,
                'type' => $n->data['type'] ?? 'info',
                'link' => $n->data['link'] ?? null,
                'created_at' => $n->created_at?->toISOString(),
                'read' => (bool) $n->read_at,
            ];
        });

        return response()->json([
            'count' => $user->unreadNotifications()->count(),
            'notifications' => $notifications,
        ]);
    }
}
