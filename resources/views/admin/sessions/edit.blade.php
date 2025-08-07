@extends('layouts.admin')

@section('title', 'End Session')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">End Parking Session</h1>
                <p class="text-slate-600">Complete the parking session for {{ $session->plate_number }}</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('admin.sessions.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors duration-200">
                    <i class="ri-arrow-left-line mr-2"></i>
                    Back to Sessions
                </a>
            </div>
        </div>
    </div>

    <!-- Session Info Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden mb-8">
        <div class="bg-gradient-to-r from-blue-600 to-purple-600 px-8 py-6">
            <div class="flex items-center justify-between">
                <div class="flex items-center">
                    <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                        <i class="ri-car-line text-2xl text-white"></i>
                    </div>
                    <div>
                        <h2 class="text-xl font-semibold text-white">{{ $session->plate_number }}</h2>
                        <p class="text-blue-100">Parking Session</p>
                    </div>
                </div>
                <div class="text-right">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-green-100 text-green-800">
                        <div class="w-2 h-2 bg-green-400 rounded-full mr-2 animate-pulse"></div>
                        Active Session
                    </div>
                </div>
            </div>
        </div>

        <div class="p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <i class="ri-time-line text-blue-600 mr-2"></i>
                        <h3 class="font-semibold text-slate-800">Start Time</h3>
                    </div>
                    <p class="text-slate-600">{{ $session->start_time->format('M d, Y') }}</p>
                    <p class="text-slate-900 font-medium">{{ $session->start_time->format('h:i A') }}</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <i class="ri-timer-line text-green-600 mr-2"></i>
                        <h3 class="font-semibold text-slate-800">Duration</h3>
                    </div>
                    <p class="text-slate-600">{{ $session->getCurrentDurationMinutes() }} minutes</p>
                    <p class="text-slate-900 font-medium">{{ $session->formatted_duration }}</p>
                </div>
                <div class="bg-slate-50 p-4 rounded-xl">
                    <div class="flex items-center mb-2">
                        <i class="ri-money-dollar-circle-line text-purple-600 mr-2"></i>
                        <h3 class="font-semibold text-slate-800">Current Fee</h3>
                    </div>
                    <p class="text-slate-600">Estimated</p>
                    <p class="text-slate-900 font-medium">₱{{ number_format($session->getEstimatedCurrentFee(), 2) }}</p>
                </div>

            </div>
        </div>
    </div>

    <!-- End Session Form -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-yellow-500 to-orange-500 px-8 py-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                    <i class="ri-stop-circle-line text-2xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-white">End Session</h2>
                    <p class="text-yellow-100">Complete the parking session and calculate final fee</p>
                </div>
            </div>
        </div>

        <form action="{{ route('admin.sessions.update', $session) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Left Column -->
                <div class="space-y-6">
                    <!-- End Time -->
                    <div>
                        <label for="end_time" class="block text-sm font-semibold text-slate-700 mb-2">
                            <i class="ri-time-line mr-2 text-orange-600"></i>
                            End Time <span class="text-red-500">*</span>
                        </label>
                        <input id="end_time" name="end_time" type="datetime-local"
                               class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all duration-200 @error('end_time') border-red-500 @enderror"
                               value="{{ old('end_time', now()->format('Y-m-d\TH:i')) }}" required>
                        @error('end_time')
                            <p class="text-red-500 text-sm mt-1 flex items-center">
                                <i class="ri-error-warning-line mr-1"></i>
                                {{ $message }}
                            </p>
                        @enderror
                        <p class="text-slate-500 text-sm mt-1">Set the time when the parking session ended</p>
                    </div>

                    <!-- Print Receipt Option -->
                    <div>
                        <label class="flex items-center">
                            <input type="checkbox" name="printed" value="1" class="w-4 h-4 text-orange-600 bg-slate-100 border-slate-300 rounded focus:ring-orange-500 focus:ring-2" {{ old('printed') ? 'checked' : '' }}>
                            <span class="ml-3 text-sm font-semibold text-slate-700">
                                <i class="ri-printer-line mr-2 text-orange-600"></i>
                                Mark as printed receipt
                            </span>
                        </label>
                        <p class="text-slate-500 text-sm mt-1 ml-7">Check this if you've printed a receipt for the customer</p>
                    </div>
                </div>

                <!-- Right Column - Fee Calculation -->
                <div class="space-y-6">
                    <div class="bg-gradient-to-r from-green-50 to-emerald-50 p-6 rounded-xl border border-green-200">
                        <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                            <i class="ri-calculator-line mr-2 text-green-600"></i>
                            Fee Calculation
                        </h3>
                        <div class="space-y-3">
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Start Time:</span>
                                <span class="font-medium">{{ $session->start_time->format('M d, h:i A') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">End Time:</span>
                                <span class="font-medium" id="calculated-end-time">{{ now()->format('M d, h:i A') }}</span>
                            </div>
                            <div class="flex justify-between items-center">
                                <span class="text-slate-600">Duration:</span>
                                <span class="font-medium" id="calculated-duration">{{ $session->getCurrentDurationMinutes() }} minutes</span>
                            </div>
                            <hr class="border-green-200">
                            <div class="flex justify-between items-center text-lg">
                                <span class="font-semibold text-slate-800">Total Fee:</span>
                                <span class="font-bold text-green-600" id="calculated-fee">₱{{ number_format($session->getEstimatedCurrentFee(), 2) }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center text-sm text-slate-600">
                    <i class="ri-information-line mr-2"></i>
                    The final fee will be calculated automatically based on the duration
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('admin.sessions.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors duration-200 font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-yellow-500 to-orange-500 hover:from-yellow-600 hover:to-orange-600 text-white rounded-xl transition-all duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center">
                        <i class="ri-stop-circle-line mr-2"></i>
                        End Session
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts'>
<script>
    $(document).ready(function() {
        // Update fee calculation when end time changes
        $('#end_time').on('change', function() {
            updateFeeCalculation();
        });

        function updateFeeCalculation() {
            const endTime = $('#end_time').val();
            if (!endTime) return;

            const startTime = new Date('{{ $session->start_time->format('Y-m-d\TH:i:s') }}');
            const endDateTime = new Date(endTime);

            if (endDateTime <= startTime) {
                $('#calculated-end-time').text('Invalid time');
                $('#calculated-duration').text('0 minutes');
                $('#calculated-fee').text('₱0.00');
                return;
            }

            const durationMs = endDateTime - startTime;
            const durationMinutes = Math.ceil(durationMs / (1000 * 60));

            // Format end time display
            $('#calculated-end-time').text(endDateTime.toLocaleDateString('en-US', {
                month: 'short',
                day: 'numeric',
                hour: 'numeric',
                minute: '2-digit',
                hour12: true
            }));

            $('#calculated-duration').text(durationMinutes + ' minutes');

            // Calculate fee (you may need to adjust this based on your rate calculation logic)
            // For now, using a simple rate calculation
            const hourlyRate = 20; // ₱20 per hour, adjust as needed
            const fee = Math.ceil(durationMinutes / 60) * hourlyRate;
            $('#calculated-fee').text('₱' + fee.toFixed(2));
        }

        // Initialize calculation on page load
        updateFeeCalculation();
    });
</script>
@endpush
