@extends('layouts.admin')

@section('title', 'Branch Details')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">{{ $branch->name }}</h1>
                    <p class="text-slate-600 mt-1">Branch code: {{ $branch->code }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.branches.edit', $branch) }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target">
                        <i class="ri-edit-line mr-2"></i>
                        Edit Branch
                    </a>
                    <a href="{{ route('admin.branches.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-slate-600 text-white font-medium rounded-lg hover:bg-slate-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Branches
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <!-- Branch Info Card -->
        <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 shadow-lg overflow-hidden mb-8">
            <div class="px-6 py-4 border-b border-slate-200 bg-gradient-to-r from-primary-50 to-blue-50">
                <h2 class="text-lg font-semibold text-slate-900">Branch Information</h2>
            </div>
            
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Branch Name</label>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg">
                            <i class="ri-building-line text-primary-600 mr-3"></i>
                            <span class="text-slate-900 font-medium">{{ $branch->name }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Branch Code</label>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg">
                            <i class="ri-code-line text-primary-600 mr-3"></i>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                {{ $branch->code }}
                            </span>
                        </div>
                    </div>
                    
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-slate-700 mb-2">Address</label>
                        <div class="flex items-start p-3 bg-slate-50 rounded-lg">
                            <i class="ri-map-pin-line text-primary-600 mr-3 mt-0.5"></i>
                            <span class="text-slate-900">{{ $branch->address }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Created Date</label>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg">
                            <i class="ri-calendar-line text-primary-600 mr-3"></i>
                            <span class="text-slate-900">{{ $branch->created_at->format('F d, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-slate-700 mb-2">Last Updated</label>
                        <div class="flex items-center p-3 bg-slate-50 rounded-lg">
                            <i class="ri-time-line text-primary-600 mr-3"></i>
                            <span class="text-slate-900">{{ $branch->updated_at->format('F d, Y \a\t g:i A') }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200 shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="ri-user-line text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Total Users</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $branch->users->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200 shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-purple-500 to-pink-500 rounded-lg flex items-center justify-center">
                        <i class="ri-shield-user-line text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Attendants</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $branch->attendants->count() }}</p>
                    </div>
                </div>
            </div>
            
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200 shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-lg flex items-center justify-center">
                        <i class="ri-admin-line text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Other Users</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $branch->users->count() - $branch->attendants->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Users Table -->
        @if($branch->users->count() > 0)
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 shadow-lg overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-200">
                    <h2 class="text-lg font-semibold text-slate-900">Branch Users</h2>
                    <p class="text-sm text-slate-600 mt-1">All users assigned to this branch</p>
                </div>
                
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-slate-200">
                        <thead class="bg-slate-50">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">User</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Email</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Role</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Status</th>
                                <th class="px-6 py-3 text-left text-xs font-medium text-slate-500 uppercase tracking-wider">Joined</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-slate-200">
                            @foreach($branch->users as $user)
                                <tr class="hover:bg-slate-50 transition-colors duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-10 h-10 bg-gradient-to-r from-primary-500 to-blue-500 rounded-full flex items-center justify-center">
                                                <span class="text-white font-medium text-sm">
                                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                                </span>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-slate-900">{{ $user->name }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-900">
                                        {{ $user->email }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($user->roles->isNotEmpty())
                                            @foreach($user->roles as $role)
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                    @if($role->name === 'admin') bg-red-100 text-red-800
                                                    @elseif($role->name === 'attendant') bg-green-100 text-green-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ ucfirst($role->name) }}
                                                </span>
                                            @endforeach
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                No Role
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Active
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-slate-500">
                                        {{ $user->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        @else
            <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 shadow-lg">
                <div class="text-center py-12">
                    <div class="w-24 h-24 bg-slate-100 rounded-full flex items-center justify-center mx-auto mb-4">
                        <i class="ri-user-line text-slate-400 text-3xl"></i>
                    </div>
                    <h3 class="text-lg font-medium text-slate-900 mb-2">No users assigned</h3>
                    <p class="text-slate-500">This branch doesn't have any users assigned yet.</p>
                </div>
            </div>
        @endif

        <!-- Action Buttons -->
        <div class="mt-8 flex flex-col sm:flex-row gap-4">
            <a href="{{ route('admin.branches.edit', $branch) }}" 
               class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-indigo-600 to-purple-600 text-white font-medium rounded-lg hover:from-indigo-700 hover:to-purple-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                <i class="ri-edit-line mr-2"></i>
                Edit Branch
            </a>
            
            @if($branch->users->count() === 0)
                <form action="{{ route('admin.branches.destroy', $branch) }}" 
                      method="POST" 
                      class="flex-1 sm:flex-none"
                      onsubmit="return confirm('Are you sure you want to delete this branch? This action cannot be undone.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-red-600 to-pink-600 text-white font-medium rounded-lg hover:from-red-700 hover:to-pink-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="ri-delete-bin-line mr-2"></i>
                        Delete Branch
                    </button>
                </form>
            @else
                <div class="flex-1 sm:flex-none">
                    <button type="button" 
                            class="w-full inline-flex items-center justify-center px-6 py-3 bg-gray-400 text-white font-medium rounded-lg cursor-not-allowed opacity-60"
                            disabled
                            title="Cannot delete branch with assigned users">
                        <i class="ri-delete-bin-line mr-2"></i>
                        Delete Branch
                    </button>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
