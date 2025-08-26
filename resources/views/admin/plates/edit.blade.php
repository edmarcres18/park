@extends('layouts.admin')

@section('title', 'Edit Plate')
@section('subtitle', 'Update plate information')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Edit Plate</h2>
            <p class="text-slate-600 mt-1">Update the plate information below</p>
        </div>
        <a href="{{ route('admin.plates.index') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors duration-200">
            <i class="ri-arrow-left-line mr-2"></i>
            Back to Plates
        </a>
    </div>

    <!-- Edit Plate Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Plate Information</h3>
            <p class="text-sm text-slate-600 mt-1">Update the details below to modify the plate record</p>
        </div>

        <form action="{{ route('admin.plates.update', $plate) }}" method="POST" class="p-6 space-y-6">
            @csrf
            @method('PUT')

            <!-- Current Plate Info -->
            <div class="bg-slate-50 border border-slate-200 rounded-xl p-4">
                <div class="flex items-center space-x-3">
                    <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                        <i class="ri-car-line text-blue-600"></i>
                    </div>
                    <div>
                        <h4 class="text-slate-800 font-semibold text-sm">Current Plate: {{ $plate->number }}</h4>
                        <p class="text-slate-600 text-sm">Created on {{ $plate->created_at ? $plate->created_at->format('M d, Y') : 'N/A' }}</p>
                    </div>
                </div>
            </div>

            <!-- Number Field -->
            <div>
                <label for="number" class="block text-sm font-medium text-slate-700 mb-2">
                    Plate Number <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="number"
                       name="number"
                       value="{{ old('number', $plate->number) }}"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('number') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Enter plate number (e.g., ABC-1234)">
                @error('number')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Owner Name Field -->
            <div>
                <label for="owner_name" class="block text-sm font-medium text-slate-700 mb-2">
                    Owner/Description <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="owner_name"
                       name="owner_name"
                       value="{{ old('owner_name', $plate->owner_name) }}"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('owner_name') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Owner name or vehicle description">
                @error('owner_name')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Vehicle Type Field -->
            <div>
                <label for="vehicle_type" class="block text-sm font-medium text-slate-700 mb-2">
                    Vehicle Type <span class="text-red-500">*</span>
                </label>
                <select id="vehicle_type"
                        name="vehicle_type"
                        class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('vehicle_type') border-red-300 focus:ring-red-500 @enderror">
                    <option value="">Select vehicle type</option>
                    <option value="Car" {{ old('vehicle_type', $plate->vehicle_type) == 'Car' ? 'selected' : '' }}>Car</option>
                    <option value="Motorcycle" {{ old('vehicle_type', $plate->vehicle_type) == 'Motorcycle' ? 'selected' : '' }}>Motorcycle</option>
                    <option value="SUV" {{ old('vehicle_type', $plate->vehicle_type) == 'SUV' ? 'selected' : '' }}>SUV</option>
                    <option value="Van" {{ old('vehicle_type', $plate->vehicle_type) == 'Van' ? 'selected' : '' }}>Van</option>
                    <option value="Truck" {{ old('vehicle_type', $plate->vehicle_type) == 'Truck' ? 'selected' : '' }}>Truck</option>
                    <option value="Bus" {{ old('vehicle_type', $plate->vehicle_type) == 'Bus' ? 'selected' : '' }}>Bus</option>
                    <option value="Electric Vehicle" {{ old('vehicle_type', $plate->vehicle_type) == 'Electric Vehicle' ? 'selected' : '' }}>Electric Vehicle</option>
                    <option value="Hybrid Vehicle" {{ old('vehicle_type', $plate->vehicle_type) == 'Hybrid Vehicle' ? 'selected' : '' }}>Hybrid Vehicle</option>
                    <option value="Vintage/Classic" {{ old('vehicle_type', $plate->vehicle_type) == 'Vintage/Classic' ? 'selected' : '' }}>Vintage/Classic</option>
                    <option value="Government" {{ old('vehicle_type', $plate->vehicle_type) == 'Government' ? 'selected' : '' }}>Government</option>
                    <option value="Diplomatic" {{ old('vehicle_type', $plate->vehicle_type) == 'Diplomatic' ? 'selected' : '' }}>Diplomatic</option>
                    <option value="Temporary/Conduction" {{ old('vehicle_type', $plate->vehicle_type) == 'Temporary/Conduction' ? 'selected' : '' }}>Temporary/Conduction</option>
                </select>
                @error('vehicle_type')
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Information Section -->
            <div class="bg-yellow-50 border border-yellow-200 rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-yellow-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ri-alert-line text-white text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-yellow-800 font-semibold text-sm mb-1">Update Information</h4>
                        <div class="text-yellow-700 text-sm space-y-2">
                            <div>
                                <strong>Important Notes:</strong>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li>• Changing the plate number will affect all related records</li>
                                    <li>• Make sure the new plate number is not already in use</li>
                                    <li>• All fields are required and must be valid</li>
                                    <li>• Changes will be logged for audit purposes</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="flex items-center justify-end space-x-4 pt-6 border-t border-slate-200">
                <a href="{{ route('admin.plates.index') }}"
                   class="px-6 py-3 border border-slate-300 text-slate-700 rounded-xl hover:bg-slate-50 transition-colors duration-200 font-medium">
                    Cancel
                </a>
                <button type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-blue-600 to-purple-600 text-white rounded-xl hover:from-blue-700 hover:to-purple-700 transition-all duration-200 font-medium flex items-center space-x-2">
                    <i class="ri-save-line"></i>
                    <span>Update Plate</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Additional Information -->
    <div class="mt-6 bg-gradient-to-r from-blue-50 to-indigo-50 border border-blue-200 rounded-xl p-6">
        <div class="flex items-start space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-blue-500 to-indigo-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="ri-history-line text-white text-lg"></i>
            </div>
            <div>
                <h4 class="text-blue-800 font-semibold text-lg mb-2">Audit Trail & History</h4>
                <div class="text-blue-700 text-sm space-y-3">
                    <div>
                        <strong>Record Information:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• Created: {{ $plate->created_at ? $plate->created_at->format('M d, Y \a\t H:i A') : 'N/A' }}</li>
                            <li>• Last Updated: {{ $plate->updated_at ? $plate->updated_at->format('M d, Y \a\t H:i A') : 'N/A' }}</li>
                            <li>• Plate ID: {{ $plate->id }}</li>
                        </ul>
                    </div>
                    <div>
                        <strong>System Integration:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• This plate may be linked to parking sessions</li>
                            <li>• Changes will be reflected across the system</li>
                            <li>• Update history is maintained for compliance</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Form validation
document.querySelector('form').addEventListener('submit', function(e) {
    const number = document.getElementById('number').value.trim();
    const ownerName = document.getElementById('owner_name').value.trim();
    const vehicleType = document.getElementById('vehicle_type').value;

    if (!number) {
        e.preventDefault();
        alert('Please enter a plate number.');
        return false;
    }

    if (!ownerName) {
        e.preventDefault();
        alert('Please enter the owner name.');
        return false;
    }

    if (!vehicleType) {
        e.preventDefault();
        alert('Please select a vehicle type.');
        return false;
    }
});

// Comprehensive vehicle type to format mapping
const vehicleTypeFormats = {
    // Standard vehicles (Car, SUV, Van, Truck, Bus): LLL-DDDD
    'Car': {
        pattern: /^([A-Z]{3})(\d{4})$/,
        format: '$1-$2',
        placeholder: 'ABC-1234',
        maxLength: 8
    },
    'SUV': {
        pattern: /^([A-Z]{3})(\d{4})$/,
        format: '$1-$2',
        placeholder: 'XYZ-5678',
        maxLength: 8
    },
    'Van': {
        pattern: /^([A-Z]{3})(\d{4})$/,
        format: '$1-$2',
        placeholder: 'DEF-9012',
        maxLength: 8
    },
    'Truck': {
        pattern: /^([A-Z]{3})(\d{4})$/,
        format: '$1-$2',
        placeholder: 'GHI-3456',
        maxLength: 8
    },
    'Bus': {
        pattern: /^([A-Z]{3})(\d{4})$/,
        format: '$1-$2',
        placeholder: 'JKL-7890',
        maxLength: 8
    },

    // Motorcycle: Multiple patterns
    'Motorcycle': {
        patterns: [
            { pattern: /^([A-Z]{2})(\d{3})([A-Z])$/, format: '$1-$2-$3', placeholder: 'AB-123-A' },
            { pattern: /^([A-Z])(\d{3})([A-Z]{2})$/, format: '$1-$2-$3', placeholder: '1-ABC-23' },
            { pattern: /^([A-Z])(\d{1})([A-Z])(\d{3})$/, format: '$1-$2-$3-$4', placeholder: 'A-1-B-234' },
            { pattern: /^([A-Z]{2})(\d{4})$/, format: '$1-$2', placeholder: 'CD-5678' },
            { pattern: /^([A-Z])(\d{2})([A-Z])(\d{3})$/, format: '$1-$2-$3-$4', placeholder: '5-AB-678' }
        ],
        placeholder: 'AB-123-A',
        maxLength: 10
    },

    // Electric Vehicle: E-LLL-DDD
    'Electric Vehicle': {
        pattern: /^(E)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'E-ABC-123',
        maxLength: 9
    },

    // Hybrid Vehicle: H-LLL-DDD
    'Hybrid Vehicle': {
        pattern: /^(H)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'H-XYZ-456',
        maxLength: 9
    },

    // Vintage/Classic: V-LLL-DDD
    'Vintage/Classic': {
        pattern: /^(V)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'V-DEF-789',
        maxLength: 9
    },

    // Government: G-LLL-DDD
    'Government': {
        pattern: /^(G)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'G-GOV-001',
        maxLength: 9
    },

    // Diplomatic: D-LLL-DDD
    'Diplomatic': {
        pattern: /^(D)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'D-DIP-002',
        maxLength: 9
    },

    // Temporary/Conduction: T-LLL-DDD
    'Temporary/Conduction': {
        pattern: /^(T)([A-Z]{3})(\d{3})$/,
        format: '$1-$2-$3',
        placeholder: 'T-TMP-003',
        maxLength: 9
    }
};

// Enhanced plate number formatting based on selected vehicle type
function formatPlateByVehicleType(value, vehicleType) {
    if (!vehicleType || !vehicleTypeFormats[vehicleType]) {
        return value; // Return as is if no vehicle type selected
    }

    const format = vehicleTypeFormats[vehicleType];

    if (vehicleType === 'Motorcycle') {
        // Try each motorcycle pattern
        for (const pattern of format.patterns) {
            if (pattern.pattern.test(value)) {
                return value.replace(pattern.pattern, pattern.format);
            }
        }
    } else {
        // Single pattern for other vehicle types
        if (format.pattern.test(value)) {
            return value.replace(format.pattern, format.format);
        }
    }

    return value; // Return as is if no pattern matches
}

// Update placeholder and maxlength based on vehicle type
function updatePlateInputAttributes() {
    const plateInput = document.getElementById('number');
    const selectedVehicleType = document.getElementById('vehicle_type').value;
    if (selectedVehicleType && vehicleTypeFormats[selectedVehicleType]) {
        const format = vehicleTypeFormats[selectedVehicleType];
        plateInput.placeholder = format.placeholder;
        plateInput.maxLength = format.maxLength;
    } else {
        plateInput.placeholder = 'Enter plate number (e.g., ABC-1234)';
        plateInput.maxLength = 10;
    }
}

// Plate number formatting
document.getElementById('number').addEventListener('input', function() {
    let value = this.value.toUpperCase();
    // Remove any characters that aren't letters, numbers, or hyphens
    value = value.replace(/[^A-Z0-9-]/g, '');

    const selectedVehicleType = document.getElementById('vehicle_type').value;

    // Apply formatting based on selected vehicle type
    if (selectedVehicleType && vehicleTypeFormats[selectedVehicleType]) {
        value = formatPlateByVehicleType(value, selectedVehicleType);
    }

    this.value = value;
});

// Auto-detect vehicle type based on plate format
document.getElementById('number').addEventListener('blur', function() {
    const value = this.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    const vehicleTypeSelect = document.getElementById('vehicle_type');

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
    } else if (/^[A-Z]{3}\d{4}$/.test(value)) {
        vehicleTypeSelect.value = 'Car'; // Default to Car for standard format
    }

    // Update input attributes after auto-detection
    updatePlateInputAttributes();
});

// Re-format plate when vehicle type changes
document.getElementById('vehicle_type').addEventListener('change', function() {
    const plateInput = document.getElementById('number');
    const value = plateInput.value.toUpperCase().replace(/[^A-Z0-9]/g, '');
    if (value) {
        const formattedValue = formatPlateByVehicleType(value, this.value);
        plateInput.value = formattedValue;
    }

    // Update input attributes
    updatePlateInputAttributes();
});

// Initialize input attributes
updatePlateInputAttributes();

// Owner name validation
document.getElementById('owner_name').addEventListener('input', function() {
    let value = this.value;
    // Remove any numbers or special characters except spaces and common name characters
    value = value.replace(/[^a-zA-Z\s.'-]/g, '');
    this.value = value;
});
</script>
@endsection
