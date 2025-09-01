@extends('layouts.admin')

@section('title', 'Create New User')
@section('subtitle', 'Add a new attendant to the system')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Create New User</h2>
            <p class="text-slate-600 mt-1">Add a new attendant to the parking management system</p>
        </div>
        <a href="{{ route('admin.users.index') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors duration-200">
            <i class="ri-arrow-left-line mr-2"></i>
            Back to Users
        </a>
    </div>

    <!-- Create User Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">User Information</h3>
            <p class="text-sm text-slate-600 mt-1">Fill in the details below to create a new attendant account</p>
        </div>

        <form action="{{ route('admin.users.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Name Field -->
            <div>
                <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                    Full Name <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="name"
                       name="name"
                       value="{{ old('name') }}"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('name') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Enter full name">
                @error('name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Email Field -->
            <div>
                <label for="email" class="block text-sm font-medium text-slate-700 mb-2">
                    Email Address <span class="text-red-500">*</span>
                </label>
                <input type="email"
                       id="email"
                       name="email"
                       value="{{ old('email') }}"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('email') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Enter email address">
                @error('email')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Field -->
            <div>
                <label for="password" class="block text-sm font-medium text-slate-700 mb-2">
                    Password <span class="text-red-500">*</span>
                </label>
                <input type="password"
                       id="password"
                       name="password"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('password') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Enter password (minimum 8 characters)">
                @error('password')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Password Confirmation Field -->
            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-slate-700 mb-2">
                    Confirm Password <span class="text-red-500">*</span>
                </label>
                <input type="password"
                       id="password_confirmation"
                       name="password_confirmation"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                       placeholder="Confirm password">
            </div>

            <!-- Status Field -->
            <div>
                <label for="status" class="block text-sm font-medium text-slate-700 mb-2">
                    Account Status <span class="text-red-500">*</span>
                </label>
                <select id="status"
                        name="status"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('status') border-red-300 focus:ring-red-500 @enderror">
                    <option value="">Select status</option>
                    <option value="active" {{ old('status') == 'active' ? 'selected' : '' }}>Active</option>
                    <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                    <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Rejected</option>
                </select>
                @error('status')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Role Field -->
            <div>
                <label for="role" class="block text-sm font-medium text-slate-700 mb-2">
                    User Role <span class="text-red-500">*</span>
                </label>
                <select id="role"
                        name="role"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('role') border-red-300 focus:ring-red-500 @enderror">
                    <option value="">Select role</option>
                    <option value="attendant" {{ old('role') == 'attendant' ? 'selected' : '' }}>Attendant</option>
                    <option value="admin" {{ old('role') == 'admin' ? 'selected' : '' }}>Administrator</option>
                </select>
                @error('role')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Branch Assignment Field -->
            <div>
                <label for="branch_id" class="block text-sm font-medium text-slate-700 mb-2">
                    Assign Branch
                </label>
                <select id="branch_id"
                        name="branch_id"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('branch_id') border-red-300 focus:ring-red-500 @enderror">
                    <option value="">No branch assigned</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}" {{ old('branch_id') == $branch->id ? 'selected' : '' }}>
                            {{ $branch->name }} ({{ $branch->code }})
                        </option>
                    @endforeach
                </select>
                @error('branch_id')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-slate-500 text-sm mt-1">Optional: Assign user to a specific branch location</p>
            </div>

            <!-- Status Information -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ri-information-line text-white text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-blue-800 font-semibold text-sm mb-1">Status & Role Information</h4>
                        <div class="text-blue-700 text-sm space-y-2">
                            <div>
                                <strong>Account Status:</strong>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li>• <strong>Active:</strong> User can immediately access the system</li>
                                    <li>• <strong>Pending:</strong> User account requires admin approval</li>
                                    <li>• <strong>Rejected:</strong> User account has been denied access</li>
                                </ul>
                            </div>
                            <div>
                                <strong>User Roles:</strong>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li>• <strong>Attendant:</strong> Can manage parking sessions and view vehicle records</li>
                                    <li>• <strong>Administrator:</strong> Full system access including user management</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-200">
                <a href="{{ route('admin.users.index') }}"
                   class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors duration-200 font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium flex items-center space-x-2">
                    <i class="ri-user-add-line"></i>
                    <span>Create User</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Additional Information -->
    <div class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
        <div class="flex items-start space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="ri-shield-check-line text-white text-lg"></i>
            </div>
            <div>
                <h4 class="text-green-800 font-semibold text-lg mb-2">Security & Permissions</h4>
                <div class="text-green-700 text-sm space-y-3">
                    <div>
                        <strong>Role-Based Access:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• <strong>Attendants:</strong> Manage parking sessions, view vehicle records, generate tickets</li>
                            <li>• <strong>Administrators:</strong> Full system access, user management, system settings, reports</li>
                        </ul>
                    </div>
                    <div>
                        <strong>Security Requirements:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• Password must be at least 8 characters long</li>
                            <li>• Email addresses must be unique across the system</li>
                            <li>• All users are assigned appropriate roles automatically</li>
                            <li>• Role permissions are enforced throughout the application</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Password strength indicator
document.getElementById('password').addEventListener('input', function() {
    const password = this.value;
    const strength = getPasswordStrength(password);
    updatePasswordStrengthIndicator(strength);
});

function getPasswordStrength(password) {
    let strength = 0;

    if (password.length >= 8) strength++;
    if (password.match(/[a-z]/)) strength++;
    if (password.match(/[A-Z]/)) strength++;
    if (password.match(/[0-9]/)) strength++;
    if (password.match(/[^a-zA-Z0-9]/)) strength++;

    return strength;
}

function updatePasswordStrengthIndicator(strength) {
    const passwordField = document.getElementById('password');
    const existingIndicator = document.getElementById('password-strength');

    if (existingIndicator) {
        existingIndicator.remove();
    }

    if (passwordField.value.length === 0) return;

    const indicator = document.createElement('div');
    indicator.id = 'password-strength';
    indicator.className = 'mt-2 text-sm';

    let color, text, bgColor;

    if (strength <= 2) {
        color = 'text-red-600';
        text = 'Weak';
        bgColor = 'bg-red-100';
    } else if (strength <= 3) {
        color = 'text-yellow-600';
        text = 'Fair';
        bgColor = 'bg-yellow-100';
    } else if (strength <= 4) {
        color = 'text-blue-600';
        text = 'Good';
        bgColor = 'bg-blue-100';
    } else {
        color = 'text-green-600';
        text = 'Strong';
        bgColor = 'bg-green-100';
    }

    indicator.innerHTML = `
        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium ${bgColor} ${color}">
            <i class="ri-shield-line mr-1"></i>
            ${text}
        </span>
    `;

    passwordField.parentNode.appendChild(indicator);
}

// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const password = document.getElementById('password').value;
    const passwordConfirmation = document.getElementById('password_confirmation').value;
    const role = document.getElementById('role').value;
    const status = document.getElementById('status').value;

    if (password !== passwordConfirmation) {
        e.preventDefault();
        alert('Password confirmation does not match.');
        return false;
    }

    if (password.length < 8) {
        e.preventDefault();
        alert('Password must be at least 8 characters long.');
        return false;
    }

    if (!role) {
        e.preventDefault();
        alert('Please select a user role.');
        return false;
    }

    if (!status) {
        e.preventDefault();
        alert('Please select an account status.');
        return false;
    }
});

// Role selection feedback
document.getElementById('role').addEventListener('change', function() {
    const role = this.value;
    const statusField = document.getElementById('status');

    // Auto-select status based on role for better UX
    if (role === 'admin') {
        // Admins are typically created as active
        if (!statusField.value) {
            statusField.value = 'active';
        }
    } else if (role === 'attendant') {
        // Attendants might be pending for approval
        if (!statusField.value) {
            statusField.value = 'pending';
        }
    }
});

// Status selection feedback
document.getElementById('status').addEventListener('change', function() {
    const status = this.value;
    const role = document.getElementById('role').value;

    // Show warning for admin users with pending/rejected status
    if (role === 'admin' && (status === 'pending' || status === 'rejected')) {
        const warningDiv = document.getElementById('admin-status-warning');
        if (!warningDiv) {
            const warning = document.createElement('div');
            warning.id = 'admin-status-warning';
            warning.className = 'mt-2 p-3 bg-yellow-50 border border-yellow-200 rounded-lg';
            warning.innerHTML = `
                <div class="flex items-center space-x-2">
                    <i class="ri-alert-line text-yellow-600"></i>
                    <span class="text-yellow-800 text-sm font-medium">Warning: Admin users typically should be active.</span>
                </div>
            `;
            this.parentNode.appendChild(warning);
        }
    } else {
        const warningDiv = document.getElementById('admin-status-warning');
        if (warningDiv) {
            warningDiv.remove();
        }
    }
});
</script>
@endsection
