@extends('layouts.admin')

@section('title', 'Pending Users')
@section('subtitle', 'Review and approve pending user registrations')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Pending Users</h2>
            <p class="text-slate-600 mt-1">Review and manage pending user registrations</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <div class="relative">
                <input type="text" id="search-pending-users" placeholder="Search pending users..."
                       class="pl-10 pr-4 py-2 bg-white border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent">
                <i class="ri-search-line absolute left-3 top-1/2 transform -translate-y-1/2 text-slate-400"></i>
            </div>
            <button id="bulk-approve-btn" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-2 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2 hidden">
                <i class="ri-check-double-line"></i>
                <span>Approve Selected</span>
            </button>
        </div>
    </div>

    <!-- Toast notifications are now handled by the admin layout -->

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gradient-to-r from-yellow-500 to-yellow-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-yellow-100 text-sm">Pending Users</p>
                    <p class="text-3xl font-bold">{{ $users->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-time-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-blue-500 to-blue-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-blue-100 text-sm">Awaiting Review</p>
                    <p class="text-3xl font-bold">{{ $users->where('created_at', '<=', now()->subDays(1))->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-eye-line text-2xl"></i>
                </div>
            </div>
        </div>

        <div class="bg-gradient-to-r from-purple-500 to-purple-600 rounded-xl p-6 text-white">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-purple-100 text-sm">New Today</p>
                    <p class="text-3xl font-bold">{{ $users->where('created_at', '>=', now()->startOfDay())->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center">
                    <i class="ri-user-add-line text-2xl"></i>
                </div>
            </div>
        </div>
    </div>

    <!-- Bulk Actions Bar -->
    <div id="bulk-actions-bar" class="bg-white rounded-xl shadow-sm border border-slate-200 p-4 hidden">
        <div class="flex items-center justify-between">
            <div class="flex items-center space-x-4">
                <span class="text-sm text-slate-600">
                    <span id="selected-count">0</span> users selected
                </span>
                <button onclick="selectAllUsers()" class="text-sm text-blue-600 hover:text-blue-800 underline">
                    Select All
                </button>
                <button onclick="deselectAllUsers()" class="text-sm text-slate-600 hover:text-slate-800 underline">
                    Deselect All
                </button>
            </div>
            <div class="flex items-center space-x-3">
                <button onclick="bulkApproveUsers()" class="bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-2 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="ri-check-double-line"></i>
                    <span>Approve Selected</span>
                </button>
                <button onclick="bulkRejectUsers()" class="bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-2 rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center space-x-2">
                    <i class="ri-close-line"></i>
                    <span>Reject Selected</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Pending Users Table -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Pending User Registrations</h3>
        </div>

        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-slate-50">
                    <tr>
                        <th class="px-6 py-4 text-left">
                            <input type="checkbox" id="select-all" class="rounded border-slate-300 text-blue-600 focus:ring-blue-500">
                        </th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Registration Date</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Time Pending</th>
                        <th class="px-6 py-4 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-slate-200">
                    @forelse ($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors duration-200">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <input type="checkbox" class="user-checkbox rounded border-slate-300 text-blue-600 focus:ring-blue-500" value="{{ $user->id }}">
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center space-x-3">
                                    <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=f59e0b&color=fff&rounded=true"
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
                            <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                {{ $user->created_at ? $user->created_at->format('M d, Y \a\t g:i A') : 'N/A' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->created_at)
                                    @php
                                        $daysPending = $user->created_at->diffInDays(now());
                                        $hoursPending = $user->created_at->diffInHours(now());
                                    @endphp
                                    @if($daysPending > 0)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="ri-time-line mr-1"></i>
                                            {{ $daysPending }} day{{ $daysPending > 1 ? 's' : '' }}
                                        </span>
                                    @elseif($hoursPending > 12)
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <i class="ri-time-line mr-1"></i>
                                            {{ $hoursPending }} hours
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <i class="ri-time-line mr-1"></i>
                                            {{ $hoursPending }} hours
                                        </span>
                                    @endif
                                @else
                                    <span class="text-slate-400">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <a href="{{ route('admin.users.show', $user) }}"
                                       class="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded-lg text-blue-700 bg-blue-100 hover:bg-blue-200 transition-colors duration-200">
                                        <i class="ri-eye-line mr-1"></i>
                                        Review
                                    </a>
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
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="flex flex-col items-center space-y-4">
                                    <div class="w-16 h-16 bg-yellow-100 rounded-full flex items-center justify-center">
                                        <i class="ri-user-add-line text-2xl text-yellow-600"></i>
                                    </div>
                                    <div>
                                        <p class="text-slate-500 text-sm">No pending users found</p>
                                        <p class="text-slate-400 text-xs mt-1">All user registrations have been reviewed</p>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Quick Actions -->
    @if($users->count() > 0)
        <div class="bg-white rounded-xl shadow-sm border border-slate-200 p-6">
            <h3 class="text-lg font-semibold text-slate-800 mb-4">Quick Actions</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <button onclick="approveAllUsers()" class="flex items-center justify-center space-x-2 p-4 bg-gradient-to-r from-green-50 to-green-100 border border-green-200 rounded-xl hover:from-green-100 hover:to-green-200 transition-all duration-200">
                    <i class="ri-check-double-line text-green-600 text-xl"></i>
                    <span class="text-green-800 font-medium">Approve All</span>
                </button>
                <button onclick="rejectAllUsers()" class="flex items-center justify-center space-x-2 p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-xl hover:from-red-100 hover:to-red-200 transition-all duration-200">
                    <i class="ri-close-line text-red-600 text-xl"></i>
                    <span class="text-red-800 font-medium">Reject All</span>
                </button>
                <a href="{{ route('admin.users.index') }}" class="flex items-center justify-center space-x-2 p-4 bg-gradient-to-r from-blue-50 to-blue-100 border border-blue-200 rounded-xl hover:from-blue-100 hover:to-blue-200 transition-all duration-200">
                    <i class="ri-arrow-left-line text-blue-600 text-xl"></i>
                    <span class="text-blue-800 font-medium">View All Users</span>
                </a>
            </div>
        </div>
    @endif
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

<script>
// Search functionality
document.getElementById('search-pending-users').addEventListener('input', function(e) {
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
const userCheckboxes = document.querySelectorAll('.user-checkbox');
const bulkActionsBar = document.getElementById('bulk-actions-bar');
const selectedCountSpan = document.getElementById('selected-count');

selectAllCheckbox.addEventListener('change', function() {
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = this.checked;
    });
    updateBulkActionsBar();
});

userCheckboxes.forEach(checkbox => {
    checkbox.addEventListener('change', updateBulkActionsBar);
});

function updateBulkActionsBar() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    const count = checkedBoxes.length;

    selectedCountSpan.textContent = count;

    if (count > 0) {
        bulkActionsBar.classList.remove('hidden');
    } else {
        bulkActionsBar.classList.add('hidden');
    }
}

function selectAllUsers() {
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = true;
    });
    selectAllCheckbox.checked = true;
    updateBulkActionsBar();
}

function deselectAllUsers() {
    userCheckboxes.forEach(checkbox => {
        checkbox.checked = false;
    });
    selectAllCheckbox.checked = false;
    updateBulkActionsBar();
}

// Individual user actions
function approveUser(userId, userName) {
    if (confirm(`Are you sure you want to approve ${userName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/status`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';

        const statusField = document.createElement('input');
        statusField.type = 'hidden';
        statusField.name = 'status';
        statusField.value = 'active';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(statusField);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectUser(userId, userName) {
    if (confirm(`Are you sure you want to reject ${userName}?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/users/${userId}/status`;

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        const methodField = document.createElement('input');
        methodField.type = 'hidden';
        methodField.name = '_method';
        methodField.value = 'PUT';

        const statusField = document.createElement('input');
        statusField.type = 'hidden';
        statusField.name = 'status';
        statusField.value = 'rejected';

        form.appendChild(csrfToken);
        form.appendChild(methodField);
        form.appendChild(statusField);
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

// Close modal when clicking outside
document.getElementById('delete-modal').addEventListener('click', function(e) {
    if (e.target === this) {
        this.classList.add('hidden');
    }
});

// Bulk actions
function bulkApproveUsers() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    if (confirm(`Are you sure you want to approve ${checkedBoxes.length} user(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-approve") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);

        checkedBoxes.forEach(checkbox => {
            const userIdField = document.createElement('input');
            userIdField.type = 'hidden';
            userIdField.name = 'user_ids[]';
            userIdField.value = checkbox.value;
            form.appendChild(userIdField);
        });

        document.body.appendChild(form);
        form.submit();
    }
}

function bulkRejectUsers() {
    const checkedBoxes = document.querySelectorAll('.user-checkbox:checked');
    if (checkedBoxes.length === 0) return;

    if (confirm(`Are you sure you want to reject ${checkedBoxes.length} user(s)?`)) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.bulk-reject") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);

        checkedBoxes.forEach(checkbox => {
            const userIdField = document.createElement('input');
            userIdField.type = 'hidden';
            userIdField.name = 'user_ids[]';
            userIdField.value = checkbox.value;
            form.appendChild(userIdField);
        });

        document.body.appendChild(form);
        form.submit();
    }
}

function approveAllUsers() {
    if (confirm('Are you sure you want to approve all pending users?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.approve-all") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

function rejectAllUsers() {
    if (confirm('Are you sure you want to reject all pending users?')) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '{{ route("admin.users.reject-all") }}';

        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        form.appendChild(csrfToken);
        document.body.appendChild(form);
        form.submit();
    }
}

// Toast notifications are now handled by the admin layout
</script>
@endsection
