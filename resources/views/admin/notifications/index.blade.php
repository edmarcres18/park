@extends('layouts.admin')

@section('title', 'Notifications')
@section('subtitle', 'Manage and view all system notifications')

@section('content')
<div class="space-y-6">
    <!-- Header -->
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-3xl font-bold text-slate-800">Notifications</h1>
            <p class="text-slate-600 mt-1">Manage all system notifications and alerts</p>
        </div>
        <div class="flex items-center space-x-3">
            <button onclick="markAllAsRead()" class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition-colors">
                <i class="ri-check-double-line mr-2"></i>
                Mark All as Read
            </button>
        </div>
    </div>

    <!-- Notifications List -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="p-6 border-b border-slate-200">
            <h2 class="text-xl font-semibold text-slate-800">All Notifications</h2>
            <p class="text-slate-600 text-sm mt-1">Showing {{ $notifications->total() }} notifications</p>
        </div>

        <div class="divide-y divide-slate-200">
            @forelse($notifications as $notification)
                <div class="p-6 hover:bg-slate-50 transition-colors {{ $notification->read_at ? 'opacity-75' : '' }}">
                    <div class="flex items-start space-x-4">
                        <div class="w-12 h-12 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0">
                            <i class="ri-user-add-line text-white text-xl"></i>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-semibold text-slate-800">New User Registered</h3>
                                    @if(!$notification->read_at)
                                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                                    @endif
                                </div>
                                <div class="flex items-center space-x-2">
                                    <span class="text-sm text-slate-500">{{ $notification->created_at->diffForHumans() }}</span>
                                    @if(!$notification->read_at)
                                        <button onclick="markAsRead('{{ $notification->id }}')" class="text-sm text-blue-600 hover:text-blue-800 underline">
                                            Mark as read
                                        </button>
                                    @endif
                                </div>
                            </div>
                            <p class="text-slate-600 mt-2">
                                <strong>{{ $notification->data['name'] ?? 'Unknown User' }}</strong> joined the platform with email
                                <strong>{{ $notification->data['email'] ?? 'No email provided' }}</strong>
                            </p>
                            <div class="mt-3 flex items-center space-x-4 text-sm text-slate-500">
                                <span>User ID: {{ $notification->data['user_id'] ?? 'N/A' }}</span>
                                <span>Created: {{ $notification->created_at->format('M d, Y \a\t g:i A') }}</span>
                                @if($notification->read_at)
                                    <span>Read: {{ $notification->read_at->format('M d, Y \a\t g:i A') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <div class="w-20 h-20 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-notification-off-line text-3xl text-slate-400"></i>
                    </div>
                    <h3 class="text-lg font-semibold text-slate-800 mb-2">No notifications found</h3>
                    <p class="text-slate-600">You don't have any notifications yet. They will appear here when new users register.</p>
                </div>
            @endforelse
        </div>

        <!-- Pagination -->
        @if($notifications->hasPages())
            <div class="p-6 border-t border-slate-200">
                {{ $notifications->links() }}
            </div>
        @endif
    </div>
</div>

<script>
    // Function to mark notification as read
    function markAsRead(notificationId) {
        const url = `/admin/notifications/${notificationId}/mark-as-read`;

        fetch(url, {
            method: 'PUT',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload the page to show updated state
                window.location.reload();
            }
        })
        .catch(error => {
            console.error('Error marking notification as read:', error);
        });
    }

    // Function to mark all notifications as read
    function markAllAsRead() {
        if (confirm('Are you sure you want to mark all notifications as read?')) {
            const url = '/admin/notifications/mark-all-as-read';

            fetch(url, {
                method: 'PUT',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    window.location.reload();
                }
            })
            .catch(error => {
                console.error('Error marking all notifications as read:', error);
            });
        }
    }
</script>
@endsection
