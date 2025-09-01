@extends('layouts.admin')

@section('title', 'User Details')
@section('subtitle', 'View and manage user information')

@section('content')
<div class="space-y-6">
    <!-- Header Section -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">User Details</h2>
            <p class="text-slate-600 mt-1">Manage user information and status</p>
        </div>
        <div class="mt-4 sm:mt-0 flex items-center space-x-3">
            <a href="{{ route('admin.users.edit', $user) }}"
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-indigo-700 text-white rounded-xl hover:from-indigo-700 hover:to-indigo-800 transition-all duration-200">
                <i class="ri-edit-line mr-2"></i>
                Edit User
            </a>
            <a href="{{ route('admin.users.index') }}"
               class="inline-flex items-center px-4 py-2 border border-slate-300 rounded-xl text-slate-700 bg-white hover:bg-slate-50 transition-colors duration-200">
                <i class="ri-arrow-left-line mr-2"></i>
                Back to Users
            </a>
        </div>
    </div>

    <!-- Status Messages -->
    @if (session('status'))
        <div class="bg-green-50 border border-green-200 rounded-xl p-4 animate-fade-in">
            <div class="flex items-center space-x-3">
                <div class="w-8 h-8 bg-green-500 rounded-full flex items-center justify-center">
                    <i class="ri-check-line text-white text-sm"></i>
                </div>
                <div>
                    <p class="text-green-800 font-medium">{{ session('status') }}</p>
                </div>
            </div>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- User Information Card -->
        <div class="lg:col-span-2">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">User Information</h3>
                </div>

                <div class="p-6">
                    <div class="flex items-center space-x-4 mb-6">
                        <img src="https://ui-avatars.com/api/?name={{ urlencode($user->name) }}&background=3b82f6&color=fff&rounded=true&size=80"
                             alt="{{ $user->name }}" class="w-20 h-20 rounded-full">
                        <div>
                            <h4 class="text-xl font-bold text-slate-800">{{ $user->name }}</h4>
                            <p class="text-slate-600">{{ $user->email }}</p>
                            <div class="mt-2">
                                @if($user->status === 'active')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                        <i class="ri-check-circle-line mr-1"></i>
                                        Active User
                                    </span>
                                @elseif($user->status === 'pending')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                        <i class="ri-time-line mr-1"></i>
                                        Pending Approval
                                    </span>
                                @elseif($user->status === 'rejected')
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                        <i class="ri-close-circle-line mr-1"></i>
                                        Rejected
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-slate-100 text-slate-800">
                                        {{ ucfirst($user->status) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Full Name</label>
                                <p class="text-slate-900 bg-slate-50 px-3 py-2 rounded-lg">{{ $user->name }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Email Address</label>
                                <p class="text-slate-900 bg-slate-50 px-3 py-2 rounded-lg">{{ $user->email }}</p>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">User Role</label>
                                <div class="bg-slate-50 px-3 py-2 rounded-lg">
                                    @if($user->hasRole('admin'))
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="ri-shield-user-line mr-1"></i>
                                            Administrator
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
                                </div>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Assigned Branch</label>
                                <div class="bg-slate-50 px-3 py-2 rounded-lg">
                                    @if($user->branch)
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center mr-3">
                                                <i class="ri-building-line text-white text-xs"></i>
                                            </div>
                                            <div>
                                                <div class="text-sm font-medium text-slate-900">{{ $user->branch->name }}</div>
                                                <div class="text-xs text-slate-500">{{ $user->branch->code }} • {{ $user->branch->address }}</div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-slate-100 text-slate-500">
                                            <i class="ri-building-line mr-1"></i>
                                            Not Assigned
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">User ID</label>
                                <p class="text-slate-900 bg-slate-50 px-3 py-2 rounded-lg">#{{ $user->id }}</p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-slate-700 mb-1">Registration Date</label>
                                <p class="text-slate-900 bg-slate-50 px-3 py-2 rounded-lg">
                                    {{ $user->created_at ? $user->created_at->format('F d, Y \a\t g:i A') : 'N/A' }}
                                </p>
                            </div>
                        </div>
                    </div>

                    @if($user->updated_at && $user->updated_at != $user->created_at)
                        <div class="mt-6 pt-6 border-t border-slate-200">
                            <label class="block text-sm font-medium text-slate-700 mb-1">Last Updated</label>
                            <p class="text-slate-900 bg-slate-50 px-3 py-2 rounded-lg">
                                {{ $user->updated_at->format('F d, Y \a\t g:i A') }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Actions Card -->
        <div class="lg:col-span-1">
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">Actions</h3>
                </div>

                <div class="p-6 space-y-4">
                    @if ($user->status === 'pending')
                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-slate-700">Approve User</h4>
                            <p class="text-xs text-slate-500">This will activate the user account and grant access to the system.</p>
                            <form action="{{ route('admin.users.update-status', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="active">
                                <button type="submit"
                                        class="w-full bg-gradient-to-r from-green-600 to-green-700 text-white px-4 py-3 rounded-xl hover:from-green-700 hover:to-green-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                    <i class="ri-check-line"></i>
                                    <span>Approve User</span>
                                </button>
                            </form>
                        </div>

                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-slate-700">Reject User</h4>
                            <p class="text-xs text-slate-500">This will reject the user registration and deny access to the system.</p>
                            <form action="{{ route('admin.users.update-status', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="rejected">
                                <button type="submit"
                                        class="w-full bg-gradient-to-r from-red-600 to-red-700 text-white px-4 py-3 rounded-xl hover:from-red-700 hover:to-red-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                    <i class="ri-close-line"></i>
                                    <span>Reject User</span>
                                </button>
                            </form>
                        </div>
                    @elseif ($user->status === 'active')
                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-slate-700">Deactivate User</h4>
                            <p class="text-xs text-slate-500">This will temporarily disable the user account.</p>
                            <form action="{{ route('admin.users.update-status', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="inactive">
                                <button type="submit"
                                        class="w-full bg-gradient-to-r from-yellow-600 to-yellow-700 text-white px-4 py-3 rounded-xl hover:from-yellow-700 hover:to-yellow-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                    <i class="ri-pause-line"></i>
                                    <span>Deactivate User</span>
                                </button>
                            </form>
                        </div>
                    @elseif ($user->status === 'rejected')
                        <div class="space-y-3">
                            <h4 class="text-sm font-medium text-slate-700">Reconsider User</h4>
                            <p class="text-xs text-slate-500">This will move the user back to pending status for reconsideration.</p>
                            <form action="{{ route('admin.users.update-status', $user) }}" method="POST" class="w-full">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="status" value="pending">
                                <button type="submit"
                                        class="w-full bg-gradient-to-r from-blue-600 to-blue-700 text-white px-4 py-3 rounded-xl hover:from-blue-700 hover:to-blue-800 transition-all duration-200 flex items-center justify-center space-x-2">
                                    <i class="ri-refresh-line"></i>
                                    <span>Reconsider User</span>
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="pt-4 border-t border-slate-200">
                        <h4 class="text-sm font-medium text-slate-700 mb-3">Quick Actions</h4>
                        <div class="space-y-2">
                            <button class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                                <i class="ri-mail-line"></i>
                                <span>Send Message</span>
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                                <i class="ri-history-line"></i>
                                <span>View Activity</span>
                            </button>
                            <button class="w-full text-left px-3 py-2 text-sm text-slate-600 hover:bg-slate-50 rounded-lg transition-colors duration-200 flex items-center space-x-2">
                                <i class="ri-file-list-line"></i>
                                <span>View Reports</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- User Stats Card -->
            <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden mt-6">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h3 class="text-lg font-semibold text-slate-800">User Statistics</h3>
                </div>

                <div class="p-6 space-y-4">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Account Age</span>
                        <span class="text-sm font-medium text-slate-900">
                            {{ $user->created_at ? $user->created_at->diffForHumans() : 'N/A' }}
                        </span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Last Login</span>
                        <span class="text-sm font-medium text-slate-900">Never</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-slate-600">Login Count</span>
                        <span class="text-sm font-medium text-slate-900">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

