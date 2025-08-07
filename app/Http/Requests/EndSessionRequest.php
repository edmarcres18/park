<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\ParkingSession;
use Carbon\Carbon;

class EndSessionRequest extends FormRequest
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
        $session = $this->route('session');
        
        return [
            'end_time' => [
                'required',
                'date',
                'after:' . ($session ? $session->start_time : 'now'),
                'before_or_equal:now',
            ],
            'printed' => 'nullable|boolean',
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'end_time.required' => 'The end time is required.',
            'end_time.after' => 'The end time must be after the session start time.',
            'end_time.before_or_equal' => 'The end time cannot be in the future.',
        ];
    }

    /**
     * Get custom attributes for validator errors.
     */
    public function attributes(): array
    {
        return [
            'end_time' => 'end time',
        ];
    }

    /**
     * Prepare the data for validation.
     */
    protected function prepareForValidation(): void
    {
        // Set end_time to now if not provided
        if (!$this->end_time) {
            $this->merge(['end_time' => now()->format('Y-m-d\TH:i')]);
        }
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $session = $this->route('session');
            
            if ($session && !$session->isActive()) {
                $validator->errors()->add('session', 'This parking session has already been ended.');
            }
        });
    }
}
