@extends('layouts.admin')

@section('title', 'Edit Parking Rate')

@section('content')
<div class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <i class="ri-edit-line text-blue-600 mr-3"></i>
                        Edit Parking Rate
                    </h1>
                    <p class="mt-2 text-gray-600">Update the parking rate configuration</p>
                    <div class="mt-1 text-sm text-gray-500">
                        Rate ID: #{{ $rate->id }} • Created: {{ $rate->created_at->format('M d, Y \a\t H:i') }}
                    </div>
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('admin.rates.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gray-600 text-white font-medium rounded-xl hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Rates
                    </a>
                </div>
            </div>
        </div>

        <!-- Current Status Alert -->
        @if($rate->is_active)
        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="ri-check-circle-line text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        This is Currently the Active Rate
                    </h3>
                    <div class="mt-1 text-sm text-green-700">
                        This rate is currently being used for all new parking sessions. Changes will take effect immediately.
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-6 bg-gradient-to-r from-gray-50 to-slate-50 border border-gray-200 rounded-xl p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="ri-pause-circle-line text-gray-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-gray-800">
                        This Rate is Currently Inactive
                    </h3>
                    <div class="mt-1 text-sm text-gray-600">
                        This rate is not being used for parking sessions. You can activate it to make it the current rate.
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Main Form Card -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="px-6 py-8 sm:px-10">
                <!-- Form Header -->
                <div class="border-b border-gray-200 pb-6 mb-8">
                    <h2 class="text-xl font-semibold text-gray-900 flex items-center">
                        <i class="ri-settings-line text-blue-600 mr-2"></i>
                        Rate Configuration
                    </h2>
                    <p class="mt-2 text-sm text-gray-600">
                        Update the parking rate details below. Only one rate can be active at a time.
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.rates.update', $rate) }}" method="POST" class="space-y-8">
                    @csrf
                    @method('PUT')
                    
                    <!-- Include the shared form partial -->
                    @include('admin.rates._form')

                    <!-- Form Actions -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                <i class="ri-save-line mr-2"></i>
                                Update Parking Rate
                            </button>
                            
                            @if(!$rate->is_active)
                                <form action="{{ route('admin.rates.activate', $rate) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit"
                                            class="inline-flex items-center justify-center px-6 py-3 bg-green-600 text-white font-medium rounded-xl hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transition-all duration-200"
                                            onclick="return confirm('Are you sure you want to activate this rate? This will deactivate all other rates.')">
                                        <i class="ri-play-circle-line mr-2"></i>
                                        Activate Rate
                                    </button>
                                </form>
                            @endif
                            
                            <a href="{{ route('admin.rates.index') }}" 
                               class="inline-flex items-center justify-center px-6 py-3 bg-gray-100 text-gray-700 font-medium rounded-xl hover:bg-gray-200 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transition-all duration-200">
                                <i class="ri-close-line mr-2"></i>
                                Cancel
                            </a>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Rate History/Info Section -->
        <div class="mt-8 grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Rate Information -->
            <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="ri-information-line text-blue-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-blue-900">Rate Information</h3>
                        <div class="mt-2 text-sm text-blue-700 space-y-1">
                            <p><strong>Current Amount:</strong> {{ $rate->formattedRateAmount }} per {{ $rate->rate_type === 'hourly' ? 'hour' : 'minute' }}</p>
                            <p><strong>Grace Period:</strong> {{ $rate->formattedGracePeriod }}</p>
                            <p><strong>Last Updated:</strong> {{ $rate->updated_at->format('M d, Y \a\t H:i') }}</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                <div class="flex items-start">
                    <div class="flex-shrink-0">
                        <i class="ri-magic-line text-purple-500 text-xl"></i>
                    </div>
                    <div class="ml-3">
                        <h3 class="text-sm font-medium text-purple-900">Quick Actions</h3>
                        <div class="mt-2 space-y-2">
                            @if(!$rate->is_active)
                                <form action="{{ route('admin.rates.activate', $rate) }}" method="POST" class="inline">
                                    @csrf
                                    @method('PUT')
                                    <button type="submit" 
                                            class="text-sm text-purple-700 hover:text-purple-900 font-medium underline"
                                            onclick="return confirm('Are you sure you want to activate this rate?')">
                                        Make this the active rate
                                    </button>
                                </form>
                            @endif
                            
                            @if(!$rate->is_active)
                                <form action="{{ route('admin.rates.destroy', $rate) }}" method="POST" class="inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="block text-sm text-red-600 hover:text-red-800 font-medium underline"
                                            onclick="return confirm('Are you sure you want to permanently delete this rate?')">
                                        Delete this rate permanently
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
