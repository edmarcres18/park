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
                       placeholder="e.g., ABC 123 or AA 12345"
                       required>
                @error('number')
                    <p class="text-red-500 text-sm mt-2"><i class="ri-error-warning-line mr-1"></i>{{ $message }}</p>
                @enderror
                <p class="text-slate-500 text-xs mt-1">Format: AAA 123, AAA 1234, or AA 12345</p>
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
                       placeholder="Full name or description"
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
                <option value="Truck" {{ old('vehicle_type') == 'Truck' ? 'selected' : '' }}>Truck</option>
                <option value="Van" {{ old('vehicle_type') == 'Van' ? 'selected' : '' }}>Van</option>
                <option value="SUV" {{ old('vehicle_type') == 'SUV' ? 'selected' : '' }}>SUV</option>
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

    // Format plate number as user types with proper validation
    plateNumberInput.addEventListener('input', function(e) {
        let value = e.target.value.toUpperCase().replace(/[^A-Z0-9\s]/g, '');

        // Auto-format based on pattern
        if (value.length > 0) {
            // Remove all spaces first
            let cleanValue = value.replace(/\s/g, '');

            // Apply formatting based on length and pattern
            if (cleanValue.length >= 3) {
                let letters = cleanValue.substring(0, 3);
                let numbers = cleanValue.substring(3);

                // Check if it's motorcycle format (2 letters + 5 digits)
                if (letters.length === 2 && numbers.length >= 5) {
                    value = letters + ' ' + numbers.substring(0, 5);
                } else if (letters.length === 3) {
                    // Car format (3 letters + 3-4 digits)
                    if (numbers.length >= 3) {
                        value = letters + ' ' + numbers.substring(0, Math.min(numbers.length, 4));
                    }
                }
            }
        }

        e.target.value = value;
    });

    // Validate plate format on blur
    plateNumberInput.addEventListener('blur', function(e) {
        const value = e.target.value.trim();
        const isValid = /^([A-Z]{3}\s?\d{3,4}|[A-Z]{2}\s?\d{5})$/.test(value);

        if (value && !isValid) {
            this.classList.add('border-red-500', 'ring-red-200');
            this.classList.remove('border-slate-300');
        } else {
            this.classList.remove('border-red-500', 'ring-red-200');
            this.classList.add('border-slate-300');
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

