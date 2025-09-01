@extends('layouts.admin')

@section('title', 'User Management')
@section('subtitle', 'Manage and monitor all system users')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">User Management</h2>
            <p class="text-slate-600 mt-1">Manage all registered users and their status</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <div class="relative">
                <input type="text" id="search-users" placeholder="Search users..."
                       class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
            <button class="bg-gradient-to-r from-blue-600 to-purple-600 text-white px-4 py-2 rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 flex items-center space-x-2">
                <i class="ri-filter-3-line"></i>
                <span>Filter</span>
            </button>
            <a href="{{ route('admin.users.create') }}"
               class="bg-gradient-to-r from-green-600 to-emerald-600 text-white px-4 py-2 rounded-xl hover:from-green-700 hover:to-emerald-700 transition-all duration-200 flex items-center space-x-2">
                <i class="ri-user-add-line"></i>
                <span>Create User</span>
            </a>
        </div>
    </div>

    <!-- Toast notifications are now handled by the admin layout -->

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Total Users</p>
                    <p class="text-3xl font-bold">{{ $users->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-team-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">Attendants</p>
                    <p class="text-3xl font-bold">{{ $users->filter(function($user) { return $user->hasRole('attendant'); })->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-user-voice-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-indigo-500 to-indigo-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-indigo-100 text-sm">Administrators</p>
                    <p class="text-3xl font-bold">{{ $users->filter(function($user) { return $user->hasRole('admin'); })->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-shield-user-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-green-500 to-green-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-green-100 text-sm">Active Users</p>
                    <p class="text-3xl font-bold">{{ $users->where('status', 'active')->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-user-check-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-4">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between space-y-3 sm:space-y-0">
            <div class="flex items-center space-x-4">
                <label class="flex items-center space-x-2">
                    <input type="checkbox" id="select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                    <span class="text-sm font-medium text-slate-700">Select All</span>
                </label>
                <span id="selected-count" class="text-sm text-slate-500 hidden">0 selected</span>
            </div>
            <div class="flex items-center space-x-2">
                <button id="bulk-approve-btn" class="bg-green-100 text-green-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-green-200 transition-colors duration-200 hidden">
                    <i class="ri-check-line mr-1"></i>
                    Approve Selected
                </button>
                <button id="bulk-reject-btn" class="bg-red-100 text-red-700 px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-200 transition-colors duration-200 hidden">
                    <i class="ri-close-line mr-1"></i>
                    Reject Selected
                </button>
                <button id="bulk-delete-btn" class="bg-red-600 text-white px-4 py-2 rounded-lg text-sm font-medium hover:bg-red-700 transition-colors duration-200 hidden">
                    <i class="ri-delete-bin-line mr-1"></i>
                    Delete Selected
                </button>
            </div>
        </div>
    </div>

    <!-- Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">All Users</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">
                            <input type="checkbox" id="select-all-table" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Branch</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Joined</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors duration-200" data-user-id="{{ $user->id }}">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="user-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="{{ $user->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff&rounded=true"
                                         alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                                    <div>
                                        <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                        <div class="text-sm text-slate-500">ID: {{ $user->id }}</div>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-slate-900">{{ $user->email }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->hasRole('admin'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                        <i class="ri-shield-user-line mr-1"></i>
                                        Admin
                                    </span>
                                @elseif($user->hasRole('attendant'))
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                        <i class="ri-user-voice-line mr-1"></i>
                                        Attendant
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ $user->roles->first() ? ucfirst($user->roles->first()->name) : 'No Role' }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->branch)
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                            <i class="ri-building-line text-white text-xs"></i>
                                        </div>
                                        <div>
                                            <div class="text-sm font-medium text-slate-900">{{ $user->branch->name }}</div>
                                            <div class="text-xs text-slate-500">{{ $user->branch->code }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                        <i class="ri-building-line mr-1"></i>
                                        Not Assigned
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                        <i class="ri-check-circle-line mr-1"></i>
                                        Active
                                    </span>
                                @elseif($user->status === 'pending')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                        <i class="ri-time-line mr-1"></i>
                                        Pending
                                    </span>
                                @elseif($user->status === 'rejected')
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                        <i class="ri-close-circle-line mr-1"></i>
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-800">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $user->created_at ? $user->created_at->format('M d, Y') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200">
                                        <i class="ri-eye-line mr-1"></i>
                                        View
                                    </a>
                                    <a href="{{ route('admin.users.edit', $user) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-indigo-700 bg-indigo-100 hover:bg-indigo-200 transition-colors duration-200">
                                        <i class="ri-edit-line mr-1"></i>
                                        Edit
                                    </a>
                                    @if($user->status === 'pending')
                                        <button onclick="approveUser({{ $user->id }}, '{{ $user->name }}')"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-green-700 bg-green-100 hover:bg-green-200 transition-colors duration-200">
                                            <i class="ri-check-line mr-1"></i>
                                            Approve
                                        </button>
                                        <button onclick="rejectUser({{ $user->id }}, '{{ $user->name }}')"
                                                class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200">
                                            <i class="ri-close-line mr-1"></i>
                                            Reject
                                        </button>
                                    @endif
                                    @if($user->id !== auth()->id())
                                        <button onclick="deleteUser({{ $user->id }}, '{{ $user->name }}')"
                                        class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-red-700 bg-red-100 hover:bg-red-200 transition-colors duration-200">
                                    <i class="ri-delete-bin-line mr-1"></i>
                                    Delete
                                </button>
                                    @else
                                        <span class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-slate-400 bg-slate-100">
                                            <i class="ri-user-line mr-1"></i>
                                            Current User
                                        </span>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-16 h-16 bg-slate-100 rounded-full flex items-center justify-center">
                                        <i class="ri-user-line text-2xl text-slate-400"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 text-sm">No users found</p>
                                        <p class="text-slate-400 text-xs mt-1">Users will appear here once they register</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Delete Confirmation Modal -->
<div id="delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-error-warning-line text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Confirm Deletion</h3>
                    <p class="text-slate-600 text-sm">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-slate-700 mb-6">
                Are you sure you want to permanently delete <span id="delete-user-name" class="font-semibold"></span>?
                This will remove all associated data including notifications and role assignments.
            </p>
            <div class="flex items-center space-x-3">
                <button id="confirm-delete" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">
                    Yes, Delete User
                </button>
                <button id="cancel-delete" class="flex-1 bg-slate-200 text-slate-700 py-2 px-4 rounded-lg hover:bg-slate-300 transition-colors duration-200 font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Bulk Delete Confirmation Modal -->
<div id="bulk-delete-modal" class="fixed inset-0 bg-black bg-opacity-50 z-50 hidden">
    <div class="flex items-center justify-center min-h-screen p-4">
        <div class="bg-white rounded-xl shadow-2xl max-w-md w-full p-6">
            <div class="flex items-center space-x-3 mb-4">
                <div class="w-10 h-10 bg-red-100 rounded-full flex items-center justify-center">
                    <i class="ri-error-warning-line text-red-600 text-xl"></i>
                </div>
                <div>
                    <h3 class="text-lg font-semibold text-slate-800">Confirm Bulk Deletion</h3>
                    <p class="text-slate-600 text-sm">This action cannot be undone</p>
                </div>
            </div>
            <p class="text-slate-700 mb-6">
                Are you sure you want to permanently delete <span id="bulk-delete-count" class="font-semibold"></span> selected user(s)?
                This will remove all associated data including notifications and role assignments.
            </p>
            <div class="flex items-center space-x-3">
                <button id="confirm-bulk-delete" class="flex-1 bg-red-600 text-white py-2 px-4 rounded-lg hover:bg-red-700 transition-colors duration-200 font-medium">
                    Yes, Delete Users
                </button>
                <button id="cancel-bulk-delete" class="flex-1 bg-slate-200 text-slate-700 py-2 px-4 rounded-lg hover:bg-slate-300 transition-colors duration-200 font-medium">
                    Cancel
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// Search functionality
document.getElementById('search-users').addEventListener('input', function(e) {
    const searchTerm = e.target.value.toLowerCase();
    const rows = document.querySelectorAll('tbody tr');

    rows.forEach(row => {
        const name = row.querySelector('td:nth-child(2)').textContent.toLowerCase();
        const email = row.querySelector('td:nth-child(3)').textContent.toLowerCase();

        if (name.includes(searchTerm) || email.includes(searchTerm)) {
            row.style.display = '';
        } else {
            row.style.display = 'none';
        }
    });
});

// Checkbox functionality
const selectAllCheckbox = document.getElementById('select-all');
const selectAllTableCheckbox = document.getElementById('select-all-table');
const userCheckboxes = document.querySelectorAll('.user-checkbox');
const selectedCountSpan = document.getElementById('selected-count');
const bulkApproveBtn = document.getElementById('bulk-approve-btn');
const bulkRejectBtn = document.getElementById('bulk-reject-btn');
const bulkDeleteBtn = document.getElementById('bulk-delete-btn');

function updateBulkActions() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkedBoxes.length;

    if (count > 0) {
        selectedCountSpan.textContent = `${count} selected`;
        selectedCountSpan.classList.remove('hidden');
        bulkApproveBtn.classList.remove('hidden');
        bulkRejectBtn.classList.remove('hidden');
        bulkDeleteBtn.classList.remove('hidden');
    } else {
        selectedCountSpan.classList.add('hidden');
        bulkApproveBtn.classList.add('hidden');
        bulkRejectBtn.classList.add('hidden');
        bulkDeleteBtn.classList.add('hidden');
    }
}

function selectAll(checked) {
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = checked;
    });
    selectAllCheckbox.checked = checked;
    selectAllTableCheckbox.checked = checked;
    updateBulkActions();
}

selectAllCheckbox.addEventListener('change', function() {
    selectAll(this.checked);
});

selectAllTableCheckbox.addEventListener('change', function() {
    selectAll(this.checked);
});

userCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActions);
});

// Bulk actions
bulkApproveBtn.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (confirm(`Are you sure you want to approve ${userIds.length} user(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-approve") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
});

bulkRejectBtn.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);

    if (confirm(`Are you sure you want to reject ${userIds.length} user(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-reject") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
});

bulkDeleteBtn.addEventListener('click', function() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const userIds = Array.from(checkedBoxes).map(cb => cb.value);

    document.getElementById('bulk-delete-count').textContent = userIds.length;
    document.getElementById('bulk-delete-modal').classList.remove('hidden');

    document.getElementById('confirm-bulk-delete').onclick = function() {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-delete") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        userIds.forEach(id => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'user_ids[]';
            input.value = id;
            form.appendChild(input);
        });

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    };
});

document.getElementById('cancel-bulk-delete').addEventListener('click', function() {
    document.getElementById('bulk-delete-modal').classList.add('hidden');
});

// Individual user actions
function approveUser(userId, userName) {
    if (confirm(`Are you sure you want to approve ${userName}?`)) {
        const form = document.createElement('form');
        form.method = 'PUT';
        form.action = `/admin/users/${userId}/status`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = 'active';

        form.appendChild(csrfToken);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectUser(userId, userName) {
    if (confirm(`Are you sure you want to reject ${userName}?`)) {
        const form = document.createElement('form');
        form.method = 'PUT';
        form.action = `/admin/users/${userId}/status`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = '{{ csrf_token() }}';

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = 'rejected';

        form.appendChild(csrfToken);
        form.appendChild(statusInput);
        document.body.appendChild(form);
        form.submit();
    }
}

function deleteUser(userId, userName) {
    console.log('Delete user called:', userId, userName);
    document.getElementById('delete-user-name').textContent = userName;
    document.getElementById('delete-modal').classList.remove('hidden');

    document.getElementById('confirm-delete').onclick = function() {
        console.log('Confirm delete clicked for user:', userId);

        // First, let's test if the user exists
        fetch(`/admin/users/${userId}/test`)
            .then(response => response.json())
            .then(data => {
                console.log('User data:', data);
                if (data.error) {
                    alert('Error: ' + data.error);
                    return;
                }

                // If user exists, proceed with deletion
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `/admin/users/${userId}`;

                const csrfToken = document.createElement('input');
                csrfToken.type = 'hidden';
                csrfToken.name = '_token';
                csrfToken.value = '{{ csrf_token() }}';

                const methodInput = document.createElement('input');
                methodInput.type = 'hidden';
                methodInput.name = '_method';
                methodInput.value = 'DELETE';

                form.appendChild(csrfToken);
                form.appendChild(methodInput);
                document.body.appendChild(form);

                console.log('Submitting form to:', form.action);
                form.submit();
            })
            .catch(error => {
                console.error('Error testing user:', error);
                alert('Error checking user. Please try again.');
            });
    };
}

document.getElementById('cancel-delete').addEventListener('click', function() {
    document.getElementById('delete-modal').classList.add('hidden');
});

// Close modals when clicking outside
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

document.getElementById('bulk-delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Toast notifications are now handled by the admin layout
</script>
@endsection

