<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ParkingRate;

class UpdateParkingRateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Check if user has admin role
        return $this->user() && $this->user()->hasRole('admin');
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'nullable|string|max:255',
            'rate_type' => 'required|in:hourly,minutely',
            'rate_amount' => 'required|numeric|min:0|regex:/^\d+(\.\d{1,2})?$/',
            'grace_period' => 'nullable|integer|min:0|max:1440', // Max 24 hours
            'is_active' => 'boolean',
            'description' => 'nullable|string|max:1000',
        ];
    }

    /**
     * Get custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'rate_type.required' => 'Please select a rate type.',
            'rate_type.in' => 'Rate type must be either hourly or per minute.',
            'rate_amount.required' => 'Rate amount is required.',
            'rate_amount.numeric' => 'Rate amount must be a valid number.',
            'rate_amount.min' => 'Rate amount cannot be negative.',
            'rate_amount.regex' => 'Rate amount can have at most 2 decimal places.',
            'grace_period.integer' => 'Grace period must be a whole number.',
            'grace_period.min' => 'Grace period cannot be negative.',
            'grace_period.max' => 'Grace period cannot exceed 1440 minutes (24 hours).',
            'name.max' => 'Rate name cannot exceed 255 characters.',
            'description.max' => 'Description cannot exceed 1000 characters.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     *
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'rate_type' => 'rate type',
            'rate_amount' => 'rate amount',
            'grace_period' => 'grace period',
            'is_active' => 'active status',
        ];
    }

    /**
     * Configure the validator instance.
     *
     * @param  \Illuminate\Validation\Validator  $validator
     * @return void
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $currentRate = $this->route('rate'); // Get the current rate being updated
            
            // If setting this rate as active, check if there's another active rate
            if ($this->boolean('is_active')) {
                $activeRate = ParkingRate::active()->where('id', '!=', $currentRate->id)->first();
                if ($activeRate) {
                    // This is handled by the model, but we can add a custom message if needed
                    // The model will automatically deactivate other rates
                }
            }
        });
    }
}
