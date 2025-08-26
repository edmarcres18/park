@extends('layouts.attendant')

@section('title', 'Add Plate')
@section('subtitle', 'Register a new vehicle plate in the system')

@section('content')
<div class="bg-white rounded-2xl p-6 shadow-lg mb-8">
    <div class="flex items-center justify-between mb-6">
        <h3 class="text-xl font-bold text-slate-800">Add New Plate</h3>
        <a href="{{ route('attendant.plates.index') }}" class="bg-gray-500 text-white py-2 px-4 rounded-xl shadow-lg hover:bg-gray-600 transition duration-200">
            <i class="ri-arrow-left-line mr-2"></i>Back to List
        </a>
    </div>

    <form action="{{ route('attendant.plates.store') }}" method="POST" id="plateForm">
        @csrf
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="mb-4">
                <label for="number" class="block text-sm font-medium text-slate-700 mb-2">
                    <i class="ri-hashtag mr-1"></i>Plate Number
                </label>
                <input type="text"
                       name="number"
                       id="number"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 @error('number') border-red-500 ring-2 ring-red-200 @enderror"
                       value="{{ old('number') }}"
                       placeholder="e.g., ABC-1234, AB-123-A, E-ABC-123"
                       required>
                @error('number')
                    <p class="text-red-500 text-sm mt-2"><i class="ri-error-warning-line mr-1"></i>{{ $message }}</p>
                @enderror
                <p class="text-slate-500 text-xs mt-1">Philippine LTO format: Standard, Motorcycle, EV, Hybrid, etc.</p>
            </div>

            <div class="mb-4">
                <label for="owner_name" class="block text-sm font-medium text-slate-700 mb-2">
                    <i class="ri-user-line mr-1"></i>Owner/Description
                </label>
                <input type="text"
                       name="owner_name"
                       id="owner_name"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 @error('owner_name') border-red-500 ring-2 ring-red-200 @enderror"
                       value="{{ old('owner_name') }}"
                       placeholder="Owner name or vehicle description"
                       required>
                @error('owner_name')
                    <p class="text-red-500 text-sm mt-2"><i class="ri-error-warning-line mr-1"></i>{{ $message }}</p>
                @enderror
            </div>
        </div>

        <div class="mb-6">
            <label for="vehicle_type" class="block text-sm font-medium text-slate-700 mb-2">
                <i class="ri-car-line mr-1"></i>Vehicle Type
            </label>
            <select name="vehicle_type"
                    id="vehicle_type"
                    class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all duration-200 @error('vehicle_type') border-red-500 ring-2 ring-red-200 @enderror"
                    required>
                <option value="">Select vehicle type</option>
                <option value="Car" {{ old('vehicle_type') == 'Car' ? 'selected' : '' }}>Car</option>
                <option value="Motorcycle" {{ old('vehicle_type') == 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                <option value="SUV" {{ old('vehicle_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
                <option value="Van" {{ old('vehicle_type') == 'Van' ? 'selected' : '' }}>Van</option>
                <option value="Truck" {{ old('vehicle_type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                <option value="Bus" {{ old('vehicle_type') == 'Bus' ? 'selected' : '' }}>Bus</option>
                <option value="Electric Vehicle" {{ old('vehicle_type') == 'Electric Vehicle' ? 'selected' : '' }}>Electric Vehicle</option>
                <option value="Hybrid Vehicle" {{ old('vehicle_type') == 'Hybrid Vehicle' ? 'selected' : '' }}>Hybrid Vehicle</option>
                <option value="Vintage/Classic" {{ old('vehicle_type') == 'Vintage/Classic' ? 'selected' : '' }}>Vintage/Classic</option>
                <option value="Government" {{ old('vehicle_type') == 'Government' ? 'selected' : '' }}>Government</option>
                <option value="Diplomatic" {{ old('vehicle_type') == 'Diplomatic' ? 'selected' : '' }}>Diplomatic</option>
                <option value="Temporary/Conduction" {{ old('vehicle_type') == 'Temporary/Conduction' ? 'selected' : '' }}>Temporary/Conduction</option>
            </select>
            @error('vehicle_type')
                <p class="text-red-500 text-sm mt-2"><i class="ri-error-warning-line mr-1"></i>{{ $message }}</p>
            @enderror
        </div>

        <div class="flex items-center justify-end space-x-4">
            <a href="{{ route('attendant.plates.index') }}" class="bg-gray-500 text-white py-3 px-6 rounded-xl shadow-lg hover:bg-gray-600 transition duration-200">
                Cancel
            </a>
            <button type="submit" class="bg-gradient-to-r from-teal-500 to-blue-600 text-white py-3 px-6 rounded-xl shadow-lg hover:from-teal-600 hover:to-blue-700 transition duration-200 flex items-center">
                <i class="ri-add-circle-line mr-2"></i>Add Plate
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const plateNumberInput = document.getElementById('number');
    const ownerNameInput = document.getElementById('owner_name');
    const vehicleTypeSelect = document.getElementById('vehicle_type');

    // Enhanced plate number formatting for Philippine LTO formats
    plateNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

        // Apply formatting based on length and patterns
        if (value.length >= 7) {
            // Standard vehicles: LLL-DDDD
            if (/^[A-Z]{3}\d{4}$/.test(value)) {
                value = value.substring(0, 3) + '-' + value.substring(3, 7);
            }
            // Special categories: E/H/V/G/D-LLL-DDD
            else if (/^[EVHGD][A-Z]{3}\d{3}$/.test(value)) {
                value = value.substring(0, 1) + '-' + value.substring(1, 4) + '-' + value.substring(4, 7);
            }
        } else if (value.length >= 6) {
            // Motorcycles: LL-DDD-L, LL-DDDD
            if (/^[A-Z]{2}\d{3}[A-Z]$/.test(value)) {
                value = value.substring(0, 2) + '-' + value.substring(2, 5) + '-' + value.substring(5, 6);
            } else if (/^[A-Z]{2}\d{4}$/.test(value)) {
                value = value.substring(0, 2) + '-' + value.substring(2, 6);
            }
        }

        e.target.value = value;
    });

    // Auto-detect vehicle type based on plate format
    plateNumberInput.addEventListener('blur', function() {
        const value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');

        if (value.startsWith('E')) {
            vehicleTypeSelect.value = 'Electric Vehicle';
        } else if (value.startsWith('H')) {
            vehicleTypeSelect.value = 'Hybrid Vehicle';
        } else if (value.startsWith('V')) {
            vehicleTypeSelect.value = 'Vintage/Classic';
        } else if (value.startsWith('G')) {
            vehicleTypeSelect.value = 'Government';
        } else if (value.startsWith('D')) {
            vehicleTypeSelect.value = 'Diplomatic';
        } else if (value.startsWith('T')) {
            vehicleTypeSelect.value = 'Temporary/Conduction';
        } else if (/^[A-Z]{2}\d{3}[A-Z]$|^[A-Z]\d{3}[A-Z]{2}$|^[A-Z]\d{1}[A-Z]\d{3}$|^[A-Z]{2}\d{4}$|^[A-Z]\d{2}[A-Z]\d{3}$/.test(value)) {
            vehicleTypeSelect.value = 'Motorcycle';
        }
    });

    // Capitalize owner name
    ownerNameInput.addEventListener('input', function(e) {
        let value = e.target.value;
        e.target.value = value.charAt(0).toUpperCase() + value.slice(1);
    });
});
</script>
@endsection

