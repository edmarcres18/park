@extends('layouts.admin')

@section('title', 'Create New Plate')
@section('subtitle', 'Add a new plate to the system')

@section('content')
<div class="max-w-4xl mx-auto">
    <!-- Header Section -->
    <div class="flex items-center justify-between mb-6">
        <div>
            <h2 class="text-2xl font-bold text-slate-800">Create New Plate</h2>
            <p class="text-slate-600 mt-1">Add a new plate to the parking management system</p>
        </div>
        <a href="{{ route('admin.plates.index') }}"
           class="inline-flex items-center px-4 py-2 bg-slate-100 text-slate-700 rounded-xl hover:bg-slate-200 transition-colors duration-200">
            <i class="ri-arrow-left-line mr-2"></i>
            Back to Plates
        </a>
    </div>

    <!-- Create Plate Form -->
    <div class="bg-white rounded-xl shadow-sm border border-slate-200 overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-200">
            <h3 class="text-lg font-semibold text-slate-800">Plate Information</h3>
            <p class="text-sm text-slate-600 mt-1">Fill in the details below to create a new plate record</p>
        </div>

        <form action="{{ route('admin.plates.store') }}" method="POST" class="p-6 space-y-6">
            @csrf

            <!-- Number Field -->
            <div>
                <label for="number" class="block text-sm font-medium text-slate-700 mb-2">
                    Plate Number <span class="text-red-500">*</span>
                </label>
                <input type="text"
                       id="number"
                       name="number"
                       value="{{ old('number') }}"
                       class="w-full px-4 py-3 border border-slate-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent @error('number') border-red-300 focus:ring-red-500 @enderror"
                       placeholder="Enter plate number (e.g., ABC-123)">
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
                       value="{{ old('owner_name') }}"
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
                    <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                @enderror
            </div>

            <!-- Information Section -->
            <div class="bg-blue-50 border border-blue-200 rounded-xl p-4">
                <div class="flex items-start space-x-3">
                    <div class="w-6 h-6 bg-blue-500 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                        <i class="ri-information-line text-white text-sm"></i>
                    </div>
                    <div>
                        <h4 class="text-blue-800 font-semibold text-sm mb-1">Plate Registration Information</h4>
                        <div class="text-blue-700 text-sm space-y-2">
                            <div>
                                <strong>Plate Number:</strong>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li>• Must be unique in the system</li>
                                    <li>• Use standard format (e.g., ABC-123, XYZ-789)</li>
                                    <li>• Cannot be changed once created</li>
                                </ul>
                            </div>
                            <div>
                                <strong>Vehicle Types:</strong>
                                <ul class="ml-4 mt-1 space-y-1">
                                    <li>• Car: Standard passenger vehicles</li>
                                    <li>• Motorcycle: Two-wheeled vehicles</li>
                                    <li>• SUV: Sport utility vehicles</li>
                                    <li>• Van: Commercial or passenger vans</li>
                                    <li>• Truck: Commercial trucks</li>
                                    <li>• Bus: Public or private buses</li>
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
                    <i class="ri-add-line"></i>
                    <span>Create Plate</span>
                </button>
            </div>
        </form>
    </div>

    <!-- Additional Information -->
    <div class="mt-6 bg-gradient-to-r from-green-50 to-emerald-50 border border-green-200 rounded-xl p-6">
        <div class="flex items-start space-x-3">
            <div class="w-10 h-10 bg-gradient-to-r from-green-500 to-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                <i class="ri-shield-check-line text-white text-lg"></i>
            </div>
            <div>
                <h4 class="text-green-800 font-semibold text-lg mb-2">Data Security & Validation</h4>
                <div class="text-green-700 text-sm space-y-3">
                    <div>
                        <strong>Validation Rules:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• All fields are required and must be filled</li>
                            <li>• Plate numbers must be unique across the system</li>
                            <li>• Owner names must be valid text without special characters</li>
                            <li>• Vehicle types are predefined for consistency</li>
                        </ul>
                    </div>
                    <div>
                        <strong>System Features:</strong>
                        <ul class="ml-4 mt-1 space-y-1">
                            <li>• Automatic timestamp recording for audit trails</li>
                            <li>• Data validation to ensure accuracy</li>
                            <li>• Integration with parking session management</li>
                            <li>• Search and filter capabilities for easy management</li>
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

// Plate number formatting
document.getElementById('number').addEventListener('input', function() {
    let value = this.value.toUpperCase();
    // Remove any characters that aren't letters, numbers, or hyphens
    value = value.replace(/[^A-Z0-9-]/g, '');
    this.value = value;
});

// Owner name validation
document.getElementById('owner_name').addEventListener('input', function() {
    let value = this.value;
    // Remove any numbers or special characters except spaces and common name characters
    value = value.replace(/[^a-zA-Z\s.'-]/g, '');
    this.value = value;
});
</script>
@endsection
