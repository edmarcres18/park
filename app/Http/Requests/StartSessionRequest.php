<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ParkingSession;

class StartSessionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['admin', 'attendant']);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'plate_number' => [
                'required',
                'string',
                'max:20',
                'regex:/^[A-Z0-9\-\s]+$/',
                function ($attribute, $value, $fail) {
                    // Check if there's already an active session for this plate number
                    $activeSession = ParkingSession::where('plate_number', $value)
                        ->whereNull('end_time')
                        ->first();
    
                    if ($activeSession) {
                        $fail('There is already an active parking session for this plate number.');
                    }
                },
            ],
            'start_time' => 'nullable|date|before_or_equal:now',
            'parking_rate_id' => 'required|integer|exists:parking_rates,id',
            'created_by' => 'required|integer|exists:users,id',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'plate_number.required' => 'The plate number is required.',
            'plate_number.regex' => 'The plate number format is invalid. Use only letters, numbers, hyphens, and spaces.',
            'parking_rate_id.required' => 'Please select a parking rate.',
            'parking_rate_id.exists' => 'The selected parking rate is invalid.',
            'start_time.before_or_equal' => 'The start time cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'plate_number' => 'plate number',
            'parking_rate_id' => 'parking rate',
            'start_time' => 'start time',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        $this->merge([
            'plate_number' => strtoupper(trim($this->plate_number ?? '')),
            'created_by' => auth()->id(),
        ]);

        // Set start_time to now if not provided
        if (!$this->start_time) {
            $this->merge(['start_time' => now()]);
        }

        // If no parking rate is selected, try to use the active rate
        if (!$this->parking_rate_id) {
            $activeRate = \App\Models\ParkingRate::getActiveRate();
            if ($activeRate) {
                $this->merge(['parking_rate_id' => $activeRate->id]);
            }
        }
    }
}
