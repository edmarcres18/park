@extends('layouts.attendant')

@section('title', 'Parking Rates')

@section('content')
<div class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 to-green-50">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <i class="ri-settings-2-line text-green-600 mr-3"></i>
                        Parking Rates
                    </h1>
                    <p class="mt-2 text-gray-600">View current parking rates and configurations</p>
                </div>
            </div>
        </div>

        <!-- Active Rate Alert -->
        @if($activeRate)
        <div class="mb-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="ri-check-circle-line text-green-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-green-800">
                        Currently Active Rate: {{ $activeRate->name ?: 'Rate #' . $activeRate->id }}
                    </h3>
                    <div class="mt-1 text-sm text-green-700">
                        <span class="font-semibold">{{ $activeRate->formattedRateAmount }}</span> 
                        per {{ $activeRate->rate_type === 'hourly' ? 'hour' : 'minute' }}
                        @if($activeRate->grace_period)
                            • {{ $activeRate->formattedGracePeriod }} grace period
                        @endif
                    </div>
                </div>
            </div>
        </div>
        @else
        <div class="mb-6 bg-gradient-to-r from-yellow-50 to-orange-50 border border-yellow-200 rounded-xl p-4">
            <div class="flex items-center">
                <div class="flex-shrink-0">
                    <i class="ri-alert-line text-yellow-400 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-yellow-800">
                        No Active Rate Set
                    </h3>
                    <div class="mt-1 text-sm text-yellow-700">
                        Please contact an administrator to set up parking rates.
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Rates Table -->
        <div class="bg-white rounded-xl shadow-xl overflow-hidden">
            @if($rates->count() > 0)
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gradient-to-r from-gray-50 to-green-50">
                            <tr>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-hashtag mr-1"></i>ID
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-price-tag-line mr-1"></i>Rate Details
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-time-line mr-1"></i>Type & Amount
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-timer-line mr-1"></i>Grace Period
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-toggle-line mr-1"></i>Status
                                </th>
                                <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-calendar-line mr-1"></i>Created
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rates as $rate)
                                <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $rate->is_active ? 'bg-green-50 hover:bg-green-100' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="w-8 h-8 bg-gradient-to-r from-green-500 to-emerald-600 rounded-full flex items-center justify-center text-white text-sm font-bold">
                                                {{ $rate->id }}
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div>
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $rate->name ?: 'Rate #' . $rate->id }}
                                                </div>
                                                @if($rate->description)
                                                    <div class="text-sm text-gray-500">{{ $rate->description }}</div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            <span class="font-semibold">{{ $rate->formattedRateAmount }}</span>
                                            <span class="text-gray-500">per {{ $rate->rate_type === 'hourly' ? 'hour' : 'minute' }}</span>
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            {{ ucfirst($rate->rate_type) }} Rate
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rate->grace_period)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                                {{ $rate->formattedGracePeriod }}
                                            </span>
                                        @else
                                            <span class="text-sm text-gray-500">No grace period</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rate->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <i class="ri-check-circle-line mr-1"></i>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <i class="ri-close-circle-line mr-1"></i>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $rate->created_at->format('M d, Y') }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-12">
                    <div class="mx-auto h-12 w-12 text-gray-400">
                        <i class="ri-settings-2-line text-4xl"></i>
                    </div>
                    <h3 class="mt-2 text-sm font-medium text-gray-900">No rates found</h3>
                    <p class="mt-1 text-sm text-gray-500">No parking rates have been configured yet.</p>
                </div>
            @endif
        </div>

        <!-- Back to Dashboard -->
        <div class="mt-8">
            <a href="{{ route('attendant.dashboard') }}" 
               class="inline-flex items-center px-4 py-2 bg-gradient-to-r from-green-600 to-emerald-600 text-white font-medium rounded-xl hover:from-green-700 hover:to-emerald-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                <i class="ri-arrow-left-line mr-2"></i>
                Back to Dashboard
            </a>
        </div>
    </div>
</div>
@endsection
