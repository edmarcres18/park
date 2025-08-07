@extends('layouts.admin')

@section('title', 'Create New Parking Rate')

@section('content')
<div class="flex-1 overflow-auto bg-gradient-to-br from-slate-50 to-blue-50">
    <div class="p-4 sm:p-6 lg:p-8">
        <!-- Header Section -->
        <div class="mb-8">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                <div class="mb-4 sm:mb-0">
                    <h1 class="text-2xl sm:text-3xl font-bold text-gray-900 flex items-center">
                        <i class="ri-add-circle-line text-blue-600 mr-3"></i>
                        Create New Parking Rate
                    </h1>
                    <p class="mt-2 text-gray-600">Set up a new parking rate configuration for your facility</p>
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
                        Configure the parking rate details below. Only one rate can be active at a time.
                    </p>
                </div>

                <!-- Form -->
                <form action="{{ route('admin.rates.store') }}" method="POST" class="space-y-8">
                    @csrf
                    
                    <!-- Include the shared form partial -->
                    @include('admin.rates._form')

                    <!-- Form Actions -->
                    <div class="pt-6 border-t border-gray-200">
                        <div class="flex flex-col sm:flex-row space-y-3 sm:space-y-0 sm:space-x-4">
                            <button type="submit" 
                                    class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white font-medium rounded-xl hover:from-blue-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 transform hover:scale-105 transition-all duration-200 shadow-lg">
                                <i class="ri-save-line mr-2"></i>
                                Create Parking Rate
                            </button>
                            
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

        <!-- Help Section -->
        <div class="mt-8 bg-gradient-to-r from-blue-50 to-indigo-50 rounded-xl p-6 border border-blue-200">
            <div class="flex items-start">
                <div class="flex-shrink-0">
                    <i class="ri-lightbulb-line text-blue-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-blue-900">Tips for Setting Up Parking Rates</h3>
                    <div class="mt-2 text-sm text-blue-700 space-y-1">
                        <p>• <strong>Hourly rates:</strong> Charge customers per hour (e.g., ₱50-₱150/hour), with partial hours rounded up</p>
                        <p>• <strong>Per-minute rates:</strong> Charge customers for each minute (e.g., ₱1-₱3/minute) of parking time</p>
                        <p>• <strong>Grace period:</strong> Offer free parking time before billing starts (common: 10-30 minutes)</p>
                        <p>• <strong>Active status:</strong> Only one rate can be active at a time across your system</p>
                        <p>• <strong>Currency:</strong> All amounts are in Philippine Peso (₱)</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
