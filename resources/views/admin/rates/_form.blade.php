{{-- Shared form partial for creating and editing parking rates --}}
<div class="space-y-6">
    {{-- Rate Name --}}
    <div>
        <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="ri-price-tag-line text-blue-500 mr-1"></i>
            Rate Name
            <span class="text-gray-400 font-normal">(Optional)</span>
        </label>
        <input type="text" 
               id="name" 
               name="name" 
               value="{{ old('name', $rate->name ?? '') }}"
               placeholder="e.g., Weekend Rate, Standard Rate, etc."
               class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white shadow-sm @error('name') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
        @error('name')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Rate Type --}}
    <div>
        <label for="rate_type" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="ri-time-line text-blue-500 mr-1"></i>
            Rate Type
            <span class="text-red-500">*</span>
        </label>
        <select id="rate_type" 
                name="rate_type" 
                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white shadow-sm @error('rate_type') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror"
                onchange="updateRateTypeInfo()">
            <option value="">Select Rate Type</option>
            @foreach($rateTypes as $value => $label)
                <option value="{{ $value }}" 
                        {{ old('rate_type', $rate->rate_type ?? '') == $value ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
        <div id="rate-type-info" class="mt-2 p-3 bg-blue-50 border border-blue-200 rounded-lg hidden">
            <div id="hourly-info" class="hidden">
                <p class="text-sm text-blue-700 flex items-center">
                    <i class="ri-information-line mr-2"></i>
                    <strong>Hourly Rate:</strong> Customers are charged per hour. Any partial hour is rounded up to a full hour.
                </p>
            </div>
            <div id="minutely-info" class="hidden">
                <p class="text-sm text-blue-700 flex items-center">
                    <i class="ri-information-line mr-2"></i>
                    <strong>Per Minute Rate:</strong> Customers are charged for each minute of parking time.
                </p>
            </div>
        </div>
        @error('rate_type')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Rate Amount --}}
    <div>
        <label for="rate_amount" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="ri-money-dollar-circle-line text-blue-500 mr-1"></i>
            Rate Amount
            <span class="text-red-500">*</span>
        </label>
        <div class="relative">
            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                <span class="text-gray-500 text-lg font-semibold">₱</span>
            </div>
            <input type="number" 
                   id="rate_amount" 
                   name="rate_amount" 
                   value="{{ old('rate_amount', $rate->rate_amount ?? '') }}"
                   step="0.01"
                   min="0"
                   placeholder="0.00"
                   class="w-full pl-8 pr-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white shadow-sm @error('rate_amount') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
        </div>
        <p class="mt-1 text-xs text-gray-500">Enter the amount to charge per hour or per minute based on rate type selected above.</p>
        @error('rate_amount')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Grace Period --}}
    <div>
        <label for="grace_period" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="ri-timer-line text-blue-500 mr-1"></i>
            Grace Period
            <span class="text-gray-400 font-normal">(Optional)</span>
        </label>
        <div class="relative">
            <input type="number" 
                   id="grace_period" 
                   name="grace_period" 
                   value="{{ old('grace_period', $rate->grace_period ?? '') }}"
                   min="0"
                   max="1440"
                   placeholder="0"
                   class="w-full px-4 py-3 pr-16 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white shadow-sm @error('grace_period') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">
            <div class="absolute inset-y-0 right-0 pr-3 flex items-center pointer-events-none">
                <span class="text-gray-500 text-sm font-medium">minutes</span>
            </div>
        </div>
        <p class="mt-1 text-xs text-gray-500">Free parking time before charging begins (0-1440 minutes). Leave empty for no grace period.</p>
        @error('grace_period')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Active Status --}}
    <div class="bg-gray-50 border border-gray-200 rounded-xl p-4">
        <div class="flex items-start space-x-3">
            <div class="flex items-center h-5">
                <input type="checkbox" 
                       id="is_active" 
                       name="is_active" 
                       value="1"
                       {{ old('is_active', $rate->is_active ?? false) ? 'checked' : '' }}
                       class="w-5 h-5 text-blue-600 bg-white border-gray-300 rounded focus:ring-blue-500 focus:ring-2 transition-colors duration-200">
            </div>
            <div class="flex-1">
                <label for="is_active" class="block text-sm font-semibold text-gray-700 cursor-pointer">
                    <i class="ri-checkbox-circle-line text-blue-500 mr-1"></i>
                    Set as Active Rate
                </label>
                <p class="text-xs text-gray-600 mt-1">
                    Only one rate can be active at a time. Activating this rate will automatically deactivate all other rates.
                </p>
            </div>
        </div>
        @error('is_active')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>

    {{-- Description --}}
    <div>
        <label for="description" class="block text-sm font-semibold text-gray-700 mb-2">
            <i class="ri-file-text-line text-blue-500 mr-1"></i>
            Description
            <span class="text-gray-400 font-normal">(Optional)</span>
        </label>
        <textarea id="description" 
                  name="description" 
                  rows="3"
                  placeholder="Additional details about this rate plan..."
                  class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-blue-500 focus:border-blue-500 transition-colors duration-200 bg-white shadow-sm resize-none @error('description') border-red-500 focus:ring-red-500 focus:border-red-500 @enderror">{{ old('description', $rate->description ?? '') }}</textarea>
        <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
        @error('description')
            <p class="mt-2 text-sm text-red-600 flex items-center">
                <i class="ri-error-warning-line mr-1"></i>
                {{ $message }}
            </p>
        @enderror
    </div>
</div>

<script>
function updateRateTypeInfo() {
    const rateType = document.getElementById('rate_type').value;
    const infoBox = document.getElementById('rate-type-info');
    const hourlyInfo = document.getElementById('hourly-info');
    const minutelyInfo = document.getElementById('minutely-info');
    
    // Hide all info boxes first
    infoBox.classList.add('hidden');
    hourlyInfo.classList.add('hidden');
    minutelyInfo.classList.add('hidden');
    
    if (rateType === 'hourly') {
        infoBox.classList.remove('hidden');
        hourlyInfo.classList.remove('hidden');
    } else if (rateType === 'minutely') {
        infoBox.classList.remove('hidden');
        minutelyInfo.classList.remove('hidden');
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function() {
    updateRateTypeInfo();
});
</script>
