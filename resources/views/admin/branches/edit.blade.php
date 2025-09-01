@extends('layouts.admin')

@section('title', 'Edit Branch')

@section('content')
<div class="min-h-screen bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-50">
    <!-- Header Section -->
    <div class="bg-white/80 backdrop-blur-sm border-b border-slate-200 sticky top-0 z-10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-6">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                <div>
                    <h1 class="text-2xl sm:text-3xl font-bold text-slate-900">Edit Branch</h1>
                    <p class="text-slate-600 mt-1">Update branch information for {{ $branch->name }}</p>
                </div>
                <div class="flex flex-col sm:flex-row gap-3">
                    <a href="{{ route('admin.branches.show', $branch) }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5 touch-target">
                        <i class="ri-eye-line mr-2"></i>
                        View Branch
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
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
        <div class="bg-white/80 backdrop-blur-sm rounded-xl border border-slate-200 shadow-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-slate-200">
                <h2 class="text-lg font-semibold text-slate-900">Branch Information</h2>
                <p class="text-sm text-slate-600 mt-1">Update the details for this branch</p>
            </div>

            <form action="{{ route('admin.branches.update', $branch) }}" method="POST" class="p-6 space-y-6">
                @csrf
                @method('PUT')

                <!-- Branch Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-slate-700 mb-2">
                        Branch Name <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="name" 
                           name="name" 
                           value="{{ old('name', $branch->name) }}"
                           class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error('name') border-red-500 @enderror"
                           placeholder="Enter branch name (e.g., Cebu Branch)"
                           required>
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Branch Code -->
                <div>
                    <label for="code" class="block text-sm font-medium text-slate-700 mb-2">
                        Branch Code <span class="text-red-500">*</span>
                    </label>
                    <input type="text" 
                           id="code" 
                           name="code" 
                           value="{{ old('code', $branch->code) }}"
                           class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error('code') border-red-500 @enderror"
                           placeholder="Enter branch code (e.g., CEB)"
                           maxlength="10"
                           style="text-transform: uppercase;"
                           required>
                    @error('code')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-sm text-slate-500">Maximum 10 characters, will be converted to uppercase</p>
                </div>

                <!-- Address -->
                <div>
                    <label for="address" class="block text-sm font-medium text-slate-700 mb-2">
                        Address <span class="text-red-500">*</span>
                    </label>
                    <textarea id="address" 
                              name="address" 
                              rows="4"
                              class="w-full px-4 py-3 border border-slate-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition-colors duration-200 @error('address') border-red-500 @enderror"
                              placeholder="Enter complete branch address"
                              required>{{ old('address', $branch->address) }}</textarea>
                    @error('address')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Action Buttons -->
                <div class="flex flex-col sm:flex-row gap-4 pt-6 border-t border-slate-200">
                    <button type="submit" 
                            class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-primary-600 to-blue-600 text-white font-medium rounded-lg hover:from-primary-700 hover:to-blue-700 transition-all duration-200 shadow-lg hover:shadow-xl transform hover:-translate-y-0.5">
                        <i class="ri-save-line mr-2"></i>
                        Update Branch
                    </button>
                    <a href="{{ route('admin.branches.show', $branch) }}" 
                       class="flex-1 sm:flex-none inline-flex items-center justify-center px-6 py-3 bg-slate-100 text-slate-700 font-medium rounded-lg hover:bg-slate-200 transition-all duration-200">
                        <i class="ri-close-line mr-2"></i>
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Branch Stats -->
        <div class="mt-8 grid grid-cols-1 sm:grid-cols-2 gap-6">
            <div class="bg-white/80 backdrop-blur-sm rounded-xl p-6 border border-slate-200 shadow-lg">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-500 rounded-lg flex items-center justify-center">
                        <i class="ri-user-line text-white text-xl"></i>
                    </div>
                    <div class="ml-4">
                        <p class="text-sm font-medium text-slate-600">Total Users</p>
                        <p class="text-2xl font-bold text-slate-900">{{ $branch->users()->count() }}</p>
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
                        <p class="text-2xl font-bold text-slate-900">{{ $branch->attendants()->count() }}</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Help Section -->
        <div class="mt-8 bg-amber-50 rounded-xl p-6 border border-amber-200">
            <div class="flex items-start">
                <div class="w-8 h-8 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                    <i class="ri-alert-line text-amber-600"></i>
                </div>
                <div class="ml-4">
                    <h3 class="text-sm font-medium text-amber-900 mb-2">Important Notes</h3>
                    <ul class="text-sm text-amber-800 space-y-1">
                        <li>• Changing the branch code may affect existing records</li>
                        <li>• Users assigned to this branch will remain unchanged</li>
                        <li>• Branch created on {{ $branch->created_at->format('F d, Y \a\t g:i A') }}</li>
                        <li>• Last updated on {{ $branch->updated_at->format('F d, Y \a\t g:i A') }}</li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Auto-uppercase the branch code
    document.getElementById('code').addEventListener('input', function(e) {
        e.target.value = e.target.value.toUpperCase();
    });
</script>
@endsection
