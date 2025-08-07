@extends('layouts.admin')

@section('title', 'Parking Rates Management')

@section('content')
<div class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <i class="ri-settings-2-line text-blue-600 mr-3"></i>
                        Parking Rates Management
                    </h1>
                    <p class="mt-2 text-gray-600">Configure and manage parking rates for your facility</p>
                </div>
                <div class="flex flex-col sm:flex-row space-y-2 sm:space-y-0 sm:space-x-3">
                    <a href="{{ route('admin.rates.create') }}" 
                       class="inline-flex items-center justify-center px-4 py-2 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="ri-add-circle-line mr-2"></i>
                        Add New Rate
                    </a>
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
                        Please activate a parking rate to enable billing functionality.
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
                        <thead class="bg-gradient-to-r from-gray-50 to-blue-50">
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
                                <th class="px-6 py-4 text-right text-xs font-semibold text-gray-600 uppercase tracking-wider">
                                    <i class="ri-settings-line mr-1"></i>Actions
                                </th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
                            @foreach($rates as $rate)
                                <tr class="hover:bg-gray-50 transition-colors duration-200 {{ $rate->is_active ? 'bg-green-50 hover:bg-green-100' : '' }}">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            #{{ $rate->id }}
                                        </div>
                                    </td>
                                    
                                    <td class="px-6 py-4">
                                        <div class="flex items-center">
                                            <div class="flex-shrink-0 w-10 h-10 bg-gradient-to-r from-blue-500 to-purple-600 rounded-full flex items-center justify-center">
                                                <i class="ri-price-tag-3-line text-white text-lg"></i>
                                            </div>
                                            <div class="ml-4">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ $rate->name ?: 'Rate #' . $rate->id }}
                                                </div>
                                                @if($rate->description)
                                                    <div class="text-sm text-gray-500 truncate max-w-xs">
                                                        {{ Str::limit($rate->description, 50) }}
                                                    </div>
                                                @endif
                                            </div>
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center space-x-2">
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium 
                                                {{ $rate->rate_type === 'hourly' ? 'bg-blue-100 text-blue-800' : 'bg-purple-100 text-purple-800' }}">
                                                <i class="{{ $rate->rate_type === 'hourly' ? 'ri-time-line' : 'ri-timer-2-line' }} mr-1"></i>
                                                {{ $rate->rateTypeLabel }}
                                            </span>
                                        </div>
                                        <div class="mt-1 text-lg font-bold text-gray-900">
                                            {{ $rate->formattedRateAmount }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">
                                            {{ $rate->formattedGracePeriod }}
                                        </div>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($rate->is_active)
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full mr-1.5 animate-pulse"></span>
                                                Active
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                <span class="w-1.5 h-1.5 bg-gray-400 rounded-full mr-1.5"></span>
                                                Inactive
                                            </span>
                                        @endif
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $rate->created_at->format('M d, Y') }}
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <div class="flex items-center justify-end space-x-2">
                                            @if(Auth::user()->hasRole('admin'))
                                                @if(!$rate->is_active)
                                                    <form action="{{ route('admin.rates.activate', $rate) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('PUT')
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-green-600 text-white text-xs font-medium rounded-lg hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-1 transition-colors duration-200"
                                                                onclick="return confirm('Are you sure you want to activate this rate? This will deactivate all other rates.')">
                                                            <i class="ri-play-circle-line mr-1"></i>
                                                            Activate
                                                        </button>
                                                    </form>
                                                @endif
                                                
                                                <a href="{{ route('admin.rates.edit', $rate) }}" 
                                                   class="inline-flex items-center px-3 py-1.5 bg-blue-600 text-white text-xs font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-1 transition-colors duration-200">
                                                    <i class="ri-edit-line mr-1"></i>
                                                    Edit
                                                </a>
                                                
                                                @if(!$rate->is_active)
                                                    <form action="{{ route('admin.rates.destroy', $rate) }}" method="POST" class="inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" 
                                                                class="inline-flex items-center px-3 py-1.5 bg-red-600 text-white text-xs font-medium rounded-lg hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-1 transition-colors duration-200"
                                                                onclick="return confirm('Are you sure you want to delete this rate? This action cannot be undone.')">
                                                            <i class="ri-delete-bin-line mr-1"></i>
                                                            Delete
                                                        </button>
                                                    </form>
                                                @endif
                                            @else
                                                <span class="inline-flex items-center px-3 py-1.5 bg-gray-400 text-white text-xs font-medium rounded-lg cursor-not-allowed opacity-60">
                                                    <i class="ri-lock-line mr-1"></i>
                                                    Admin Only
                                                </span>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <!-- Empty State -->
                <div class="text-center py-12">
                    <div class="mx-auto w-24 h-24 bg-gradient-to-r from-blue-100 to-purple-100 rounded-full flex items-center justify-center mb-4">
                        <i class="ri-settings-2-line text-3xl text-blue-600"></i>
                    </div>
                    <h3 class="text-lg font-medium text-gray-900 mb-2">No Parking Rates Found</h3>
                    <p class="text-gray-500 mb-6">Get started by creating your first parking rate configuration.</p>
                    <a href="{{ route('admin.rates.create') }}" 
                       class="inline-flex items-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                        <i class="ri-add-circle-line mr-2"></i>
                        Create First Rate
                    </a>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
