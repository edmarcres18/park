@extends('layouts.attendant')

@section('title', 'Start New Session')

@section('content')
<div class="flex-1 overflow-y-auto p-8">
    <!-- Page Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-slate-900 mb-2">Start New Parking Session</h1>
                <p class="text-slate-600">Create a new parking session for a vehicle</p>
            </div>
            <div class="flex items-center space-x-3">
                <a href="{{ route('attendant.sessions.index') }}" class="inline-flex items-center px-4 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors duration-200">
                    <i class="ri-arrow-left-line mr-2"></i>
                    Back to Sessions
                </a>
            </div>
        </div>
    </div>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-xl border border-slate-200 overflow-hidden">
        <div class="bg-gradient-to-r from-cyan-600 to-blue-600 px-8 py-6">
            <div class="flex items-center">
                <div class="w-12 h-12 bg-white/20 rounded-xl flex items-center justify-center mr-4">
                    <i class="ri-add-circle-line text-2xl text-white"></i>
                </div>
                <div>
                    <h2 class="text-xl font-semibold text-white">New Session Details</h2>
                    <p class="text-cyan-100">Fill in the information below to start a parking session</p>
                </div>
            </div>
        </div>

        <form action="{{ route('attendant.sessions.store') }}" method="POST" class="p-8">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- Plate Number -->
                <div>
                    <label for="plate_number" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="ri-car-line mr-2 text-cyan-600"></i>
                        Plate Number <span class="text-red-500">*</span>
                    </label>
                    <select id="plate_number" name="plate_number" class="select2 w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 @error('plate_number') border-red-500 @enderror" required>
                        <option value="">Search or select a plate number...</option>
                        @foreach($plates as $plate)
                            <option value="{{ $plate->number }}" data-owner="{{ $plate->owner_name }}" data-vehicle="{{ $plate->vehicle_type }}">
                                {{ $plate->number }} - {{ $plate->owner_name }} ({{ $plate->vehicle_type }})
                            </option>
                        @endforeach
                    </select>
                    @error('plate_number')
                        <p class="text-red-500 text-sm mt-1 flex items-center">
                            <i class="ri-error-warning-line mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-slate-500 text-sm mt-1">Type to search existing plates or enter a new one</p>
                </div>

                <!-- Parking Rate -->
                <div>
                    <label for="parking_rate_id" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="ri-price-tag-3-line mr-2 text-cyan-600"></i>
                        Parking Rate <span class="text-red-500">*</span>
                    </label>
                    <select id="parking_rate_id" name="parking_rate_id" class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 @error('parking_rate_id') border-red-500 @enderror" required>
                        <option value="">Select a parking rate...</option>
                        @foreach($parkingRates as $rate)
                            <option value="{{ $rate->id }}"
                                    data-rate-type="{{ $rate->rate_type }}"
                                    data-rate-amount="{{ $rate->rate_amount }}"
                                    data-grace-period="{{ $rate->grace_period }}"
                                    {{ old('parking_rate_id', $activeRate?->id) == $rate->id ? 'selected' : '' }}>
                                {{ $rate->name ?: 'Rate #' . $rate->id }} - {{ $rate->formatted_rate_amount }} per {{ $rate->rate_type_label }}
                                @if($rate->grace_period) ({{ $rate->formatted_grace_period }} grace) @endif
                            </option>
                        @endforeach
                    </select>
                    @error('parking_rate_id')
                        <p class="text-red-500 text-sm mt-1 flex items-center">
                            <i class="ri-error-warning-line mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    @if($activeRate)
                        <p class="text-green-600 text-sm mt-1 flex items-center">
                            <i class="ri-check-circle-line mr-1"></i>
                            Currently active rate pre-selected
                        </p>
                    @else
                        <p class="text-amber-600 text-sm mt-1 flex items-center">
                            <i class="ri-alert-line mr-1"></i>
                            No active rate found - please select one
                        </p>
                    @endif
                </div>

                <!-- Start Time -->
                <div>
                    <label for="start_time" class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="ri-time-line mr-2 text-cyan-600"></i>
                        Start Time
                    </label>
                    <input id="start_time" name="start_time" type="datetime-local"
                           class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-cyan-500 focus:border-cyan-500 transition-all duration-200 @error('start_time') border-red-500 @enderror"
                           value="{{ old('start_time', now()->format('Y-m-d\TH:i')) }}">
                    @error('start_time')
                        <p class="text-red-500 text-sm mt-1 flex items-center">
                            <i class="ri-error-warning-line mr-1"></i>
                            {{ $message }}
                        </p>
                    @enderror
                    <p class="text-slate-500 text-sm mt-1">Leave blank to use current time</p>
                </div>
            </div>

            <!-- Session Preview Card -->
            <div id="session-preview" class="mt-8 p-6 bg-gradient-to-r from-cyan-50 to-blue-50 rounded-xl border border-cyan-200 hidden">
                <h3 class="text-lg font-semibold text-slate-800 mb-4 flex items-center">
                    <i class="ri-eye-line mr-2 text-cyan-600"></i>
                    Session Preview
                </h3>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-slate-600">Plate Number</p>
                        <p id="preview-plate" class="font-semibold text-slate-900">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-slate-600">Start Time</p>
                        <p id="preview-time" class="font-semibold text-slate-900">-</p>
                    </div>
                    <div class="bg-white p-4 rounded-lg shadow-sm">
                        <p class="text-sm text-slate-600">Parking Rate</p>
                        <p id="preview-rate" class="font-semibold text-slate-900">-</p>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-between mt-8 pt-6 border-t border-slate-200">
                <div class="flex items-center text-sm text-slate-600">
                    <i class="ri-information-line mr-2"></i>
                    Fields marked with <span class="text-red-500 font-semibold">*</span> are required
                </div>
                <div class="flex items-center space-x-4">
                    <a href="{{ route('attendant.sessions.index') }}" class="px-6 py-3 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl transition-colors duration-200 font-medium">
                        Cancel
                    </a>
                    <button type="submit" class="px-8 py-3 bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-700 hover:to-blue-700 text-white rounded-xl transition-all duration-200 font-semibold shadow-lg hover:shadow-xl flex items-center">
                        <i class="ri-play-circle-line mr-2"></i>
                        Start Session
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection

@push('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
    .select2-container--default .select2-selection--single {
        height: 48px !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        padding: 0 16px !important;
        display: flex !important;
        align-items: center !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__rendered {
        padding: 0 !important;
        line-height: 48px !important;
        color: #334155 !important;
    }
    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 46px !important;
        right: 16px !important;
    }
    .select2-dropdown {
        border: 1px solid #cbd5e1 !important;
        border-radius: 12px !important;
        box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1) !important;
    }
    .select2-container--default .select2-results__option--highlighted {
        background-color: #06b6d4 !important;
    }
    .select2-container--default.select2-container--focus .select2-selection--single {
        border-color: #06b6d4 !important;
        box-shadow: 0 0 0 2px rgba(6, 182, 212, 0.2) !important;
    }
</style>
@endpush

@push('scripts')
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        // Initialize Select2
        $('#plate_number').select2({
            placeholder: 'Search or select a plate number...',
            allowClear: true,
            tags: true,
            createTag: function (params) {
                const term = $.trim(params.term);
                if (term === '') {
                    return null;
                }
                return {
                    id: term.toUpperCase(),
                    text: term.toUpperCase() + ' (New)',
                    newTag: true
                };
            },
            templateResult: function(data) {
                if (data.newTag) {
                    return $('<span><i class="ri-add-line mr-2"></i>' + data.text + '</span>');
                }
                return data.text;
            }
        });

        // Update preview on form changes
        $('input, select').on('change keyup', updatePreview);
        $('#calc_hours, #calc_minutes').on('change keyup', calculateRate);
        $('#parking_rate_id').on('change', function() {
            updatePreview();
            calculateRate();
        });

        function updatePreview() {
            const plateNumber = $('#plate_number').val();
            const startTime = $('#start_time').val();
            const rateOption = $('#parking_rate_id option:selected');
            const rateName = rateOption.text().split(' - ')[0] || 'Not selected';

            if (plateNumber) {
                $('#session-preview').removeClass('hidden');
                $('#preview-plate').text(plateNumber);
                $('#preview-time').text(startTime ? new Date(startTime).toLocaleString() : 'Current time');
                $('#preview-rate').text(rateName);
            } else {
                $('#session-preview').addClass('hidden');
            }
        }

        function calculateRate() {
            const rateOption = $('#parking_rate_id option:selected');
            const rateType = rateOption.data('rate-type');
            const rateAmount = parseFloat(rateOption.data('rate-amount')) || 0;
            const gracePeriod = parseInt(rateOption.data('grace-period')) || 0;

            const hours = parseInt($('#calc_hours').val()) || 0;
            const minutes = parseInt($('#calc_minutes').val()) || 0;
            const totalMinutes = (hours * 60) + minutes;

            if (!rateType || !rateAmount || totalMinutes === 0) {
                $('#estimated_total').text('₱0.00');
                $('#rate_breakdown').html('<div>Select a rate and duration to see calculation</div>');
                return;
            }

            let chargeableMinutes = Math.max(0, totalMinutes - gracePeriod);
            let estimatedTotal = 0;
            let breakdown = [];

            if (gracePeriod > 0) {
                const graceUsed = Math.min(totalMinutes, gracePeriod);
                breakdown.push(`Grace period: ${graceUsed} min (free)`);
            }

            if (chargeableMinutes > 0) {
                if (rateType === 'hourly') {
                    const chargeableHours = Math.ceil(chargeableMinutes / 60);
                    estimatedTotal = chargeableHours * rateAmount;
                    breakdown.push(`Chargeable: ${chargeableHours} hour(s) × ₱${rateAmount.toFixed(2)}`);
                } else {
                    estimatedTotal = chargeableMinutes * rateAmount;
                    breakdown.push(`Chargeable: ${chargeableMinutes} min × ₱${rateAmount.toFixed(2)}`);
                }
            } else {
                breakdown.push('Within grace period - no charge');
            }

            $('#estimated_total').text('₱' + estimatedTotal.toFixed(2));
            $('#rate_breakdown').html(breakdown.map(item => `<div>${item}</div>`).join(''));
        }

        // Initialize calculations on page load
        updatePreview();
        calculateRate();

        // Form validation
        $('form').on('submit', function(e) {
            const plateNumber = $('#plate_number').val();
            if (!plateNumber) {
                e.preventDefault();
                $('#plate_number').next('.select2-container').addClass('border-red-500');
                // Show toast notification if available
                if (window.showToast) {
                    showToast('Please select a plate number', 'error');
                }
                return false;
            }
        });
    });
</script>
@endpush
