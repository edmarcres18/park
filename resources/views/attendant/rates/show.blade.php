@extends('layouts.attendant')

@section('title', 'Rate Details')

@section('content')
<div class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 to-green-50">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <i class="ri-settings-2-line text-green-600 mr-3"></i>
                        Rate Details
                    </h1>
                    <p class="mt-2 text-gray-600">View detailed information about this parking rate</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('attendant.rates.index') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-gray-600 to-slate-600 text-white font-medium rounded-xl hover:from-gray-700 hover:to-slate-700 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Rates
                    </a>
                </div>
            </div>
        </div>

        <!-- Rate Details Card -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            <div class="p-6">
                <!-- Rate Header -->
                <div class="flex items-center justify-between mb-6">
                    <div class="flex items-center">
                        <div class="w-12 h-12 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-xl font-bold mr-4">
                            {{ $rate->id }}
                        </div>
                        <div>
                            <h2 class="text-2xl font-bold text-gray-900">
                                {{ $rate->name ?: 'Rate #' . $rate->id }}
                            </h2>
                            @if($rate->description)
                                <p class="text-gray-600 mt-1">{{ $rate->description }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        @if($rate->is_active)
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <i class="ri-check-circle-line mr-1"></i>
                                Active
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <i class="ri-close-circle-line mr-1"></i>
                                Inactive
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Rate Information Grid -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Rate Amount -->
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 rounded-xl p-6 border border-green-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center mr-3">
                                <i class="ri-money-dollar-circle-line text-green-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Rate Amount</h3>
                        </div>
                        <div class="text-3xl font-bold text-green-600">
                            {{ $rate->formattedRateAmount }}
                        </div>
                        <p class="text-gray-600 mt-2">per {{ $rate->rate_type === 'hourly' ? 'hour' : 'minute' }}</p>
                    </div>

                    <!-- Rate Type -->
                    <div class="bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center mr-3">
                                <i class="ri-time-line text-blue-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Rate Type</h3>
                        </div>
                        <div class="text-2xl font-bold text-blue-600">
                            {{ ucfirst($rate->rate_type) }}
                        </div>
                        <p class="text-gray-600 mt-2">{{ $rate->rate_type === 'hourly' ? 'Charged per hour' : 'Charged per minute' }}</p>
                    </div>

                    <!-- Grace Period -->
                    <div class="bg-gradient-to-r from-purple-50 to-pink-50 rounded-xl p-6 border border-purple-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-purple-100 rounded-full flex items-center justify-center mr-3">
                                <i class="ri-timer-line text-purple-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Grace Period</h3>
                        </div>
                        @if($rate->grace_period)
                            <div class="text-2xl font-bold text-purple-600">
                                {{ $rate->formattedGracePeriod }}
                            </div>
                            <p class="text-gray-600 mt-2">Free parking time before billing starts</p>
                        @else
                            <div class="text-2xl font-bold text-gray-400">
                                None
                            </div>
                            <p class="text-gray-600 mt-2">No grace period configured</p>
                        @endif
                    </div>

                    <!-- Created Date -->
                    <div class="bg-gradient-to-r from-orange-50 to-red-50 rounded-xl p-6 border border-orange-200">
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center mr-3">
                                <i class="ri-calendar-line text-orange-600 text-xl"></i>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900">Created</h3>
                        </div>
                        <div class="text-2xl font-bold text-orange-600">
                            {{ $rate->created_at->format('M d, Y') }}
                        </div>
                        <p class="text-gray-600 mt-2">{{ $rate->created_at->format('g:i A') }}</p>
                    </div>
                </div>

                <!-- Additional Information -->
                @if($rate->description)
                <div class="mt-8 bg-gray-50 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-gray-900 mb-4">Description</h3>
                    <p class="text-gray-700">{{ $rate->description }}</p>
                </div>
                @endif

                <!-- Back to Dashboard -->
                <div class="mt-8 pt-6 border-t border-gray-200">
                    <a href="{{ route('attendant.dashboard') }}" 
                       class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="ri-arrow-left-line mr-2"></i>
                        Back to Dashboard
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
