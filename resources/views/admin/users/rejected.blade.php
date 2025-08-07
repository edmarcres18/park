@extends('layouts.admin')

@section('title', 'Rejected Users')
@section('subtitle', 'View and manage rejected user registrations')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Rejected Users</h2>
            <p class="text-slate-600 mt-1">View and manage rejected user registrations</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <div class="relative">
                <input type="text" id="search-rejected-users" placeholder="Search rejected users..."
                       class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
        </div>
    </div>

    <!-- Toast notifications are now handled by the admin layout -->

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-red-500 to-red-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-red-100 text-sm">Rejected Users</p>
                    <p class="text-3xl font-bold">{{ $users->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-user-forbid-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-orange-100 text-sm">Rejected This Week</p>
                    <p class="text-3xl font-bold">{{ $users->where('updated_at', '>=', now()->startOfWeek())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-calendar-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-gray-500 to-gray-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-gray-100 text-sm">Rejected This Month</p>
                    <p class="text-3xl font-bold">{{ $users->where('updated_at', '>=', now()->startOfMonth())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-calendar-2-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50 border-b border-slate-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            User
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Email
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Registration Date
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Rejected Date
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            Actions
                        </th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center">
                                    <div class="w-10 h-10 bg-gradient-to-r from-red-500 to-red-600 rounded-full flex items-center justify-center">
                                        <span class="text-white font-semibold text-sm">{{ strtoupper(substr($user->name, 0, 2)) }}</span>
                                    </div>
                                    <div class="ml-4">
                                        <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                        <div class="text-sm text-slate-500">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $user->created_at->format('M d, Y') }}</div>
                                <div class="text-sm text-slate-500">{{ $user->created_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $user->updated_at->format('M d, Y') }}</div>
                                <div class="text-sm text-slate-500">{{ $user->updated_at->format('h:i A') }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="text-blue-600 hover:text-blue-900 bg-blue-50 hover:bg-blue-100 px-3 py-1 rounded-lg transition-colors duration-200">
                                        <i class="ri-eye-line mr-1"></i>
                                        View
                                    </a>
                                    <button onclick="approveUser({{ $user->id }}, '{{ $user->name }}')"
                                            class="text-green-600 hover:text-green-900 bg-green-50 hover:bg-green-100 px-3 py-1 rounded-lg transition-colors duration-200">
                                        <i class="ri-check-line mr-1"></i>
                                        Approve
                                    </button>
                                    @if($user->id !== auth()->id())
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                                class="text-red-600 hover:text-red-900 bg-red-50 hover:bg-red-100 px-3 py-1 rounded-lg transition-colors duration-200">
                                            <i class="ri-delete-bin-line mr-1"></i>
                                            Delete
                                        </button>
                                    @else
                                        <span class="text-slate-400 bg-slate-50 px-3 py-1 rounded-lg">
                                            <i class="ri-user-line mr-1"></i>
                                            Current User
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center mb-4">
                                        <i class="ri-user-forbid-line text-2xl text-slate-400"></i>
                                    </div>
                                    <h3 class="text-lg font-medium text-slate-900 mb-2">No Rejected Users</h3>
                                    <p class="text-slate-500 text-sm">There are no rejected user registrations at the moment.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approve User Modal -->
<div id="approve-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center">
                <i class="ri-check-line text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Approve User</h3>
                <p class="text-sm text-slate-600">Are you sure you want to approve this user?</p>
            </div>
        </div>
        <p class="text-slate-700 mb-6">This will change the user's status from rejected to active, allowing them to access the system.</p>
        <div class="flex items-center justify-end space-x-3">
            <button onclick="closeApproveModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                Cancel
            </button>
            <button id="confirm-approve-btn" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-2 rounded-lg hover:from-green-700 hover:to-green-800 transition-all duration-200">
                Approve User
            </button>
        </div>
    </div>
</div>

<!-- Delete User Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
        <div class="flex items-center space-x-3 mb-4">
            <div class="w-10 h-10 bg-red-500 rounded-full flex items-center justify-center">
                <i class="ri-delete-bin-line text-white"></i>
            </div>
            <div>
                <h3 class="text-lg font-semibold text-slate-900">Delete User</h3>
                <p class="text-sm text-slate-600">Are you sure you want to delete this user?</p>
            </div>
        </div>
        <p class="text-slate-700 mb-6">This action cannot be undone. The user will be permanently removed from the system.</p>
        <div class="flex items-center justify-end space-x-3">
            <button onclick="closeDeleteModal()" class="px-4 py-2 text-slate-600 hover:text-slate-800 transition-colors">
                Cancel
            </button>
            <button id="confirm-delete-btn" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-lg hover:from-red-700 hover:to-red-800 transition-all duration-200">
                Delete User
            </button>
        </div>
    </div>
</div>

<script>
    let currentUserId = null;

    // Search functionality
    document.getElementById('search-rejected-users').addEventListener('input', function(e) {
        const searchTerm = e.target.value.toLowerCase();
        const rows = document.querySelectorAll('tbody tr');

        rows.forEach(row => {
            const name = row.querySelector('td:first-child').textContent.toLowerCase();
            const email = row.querySelector('td:nth-child(2)').textContent.toLowerCase();

            if (name.includes(searchTerm) || email.includes(searchTerm)) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
    });

    // Approve user functions
    function approveUser(userId, userName) {
        currentUserId = userId;
        document.getElementById('approve-modal').classList.remove('hidden');
    }

    function closeApproveModal() {
        document.getElementById('approve-modal').classList.add('hidden');
        currentUserId = null;
    }

    // Delete user functions
    function deleteUser(userId, userName) {
        currentUserId = userId;
        document.getElementById('delete-modal').classList.remove('hidden');
    }

    function closeDeleteModal() {
        document.getElementById('delete-modal').classList.add('hidden');
        currentUserId = null;
    }

    // Confirm approve action
    document.getElementById('confirm-approve-btn').addEventListener('click', function() {
        if (currentUserId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${currentUserId}/status`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const statusInput = document.createElement('input');
            statusInput.type = 'hidden';
            statusInput.name = 'status';
            statusInput.value = 'active';

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'PUT';

            form.appendChild(csrfToken);
            form.appendChild(statusInput);
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // Confirm delete action
    document.getElementById('confirm-delete-btn').addEventListener('click', function() {
        if (currentUserId) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/users/${currentUserId}`;

            const csrfToken = document.createElement('input');
            csrfToken.type = 'hidden';
            csrfToken.name = '_token';
            csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

            const methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            methodInput.value = 'DELETE';

            form.appendChild(csrfToken);
            form.appendChild(methodInput);

            document.body.appendChild(form);
            form.submit();
        }
    });

    // Close modals when clicking outside
    document.getElementById('approve-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeApproveModal();
        }
    });

    document.getElementById('delete-modal').addEventListener('click', function(e) {
        if (e.target === this) {
            closeDeleteModal();
        }
    });

    // Toast notifications are now handled by the admin layout
</script>
@endsection
