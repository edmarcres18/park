<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use App\Models\Plate;

class UpdatePlateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'number' => [
                'required',
                'string',
                'max:255',
                'unique:plates,number,' . $this->route('plate'),
                function ($attribute, $value, $fail) {
                    if (!Plate::isValidFormat($value)) {
                        $fail('The plate number format is not valid for Philippine LTO standards.');
                    }
                }
            ],
            'owner_name' => 'nullable|string|max:255',
            'vehicle_type' => 'required|string|max:255',
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
            'number.unique' => 'A plate with this number already exists.',
            'number.required' => 'Plate number is required.',
            'number.string' => 'Plate number must be a string.',
            'number.max' => 'Plate number cannot exceed 255 characters.',
            'owner_name.string' => 'Owner/Description must be a string.',
            'owner_name.max' => 'Owner/Description cannot exceed 255 characters.',
            'vehicle_type.required' => 'Vehicle type is required.',
            'vehicle_type.string' => 'Vehicle type must be a string.',
            'vehicle_type.max' => 'Vehicle type cannot exceed 255 characters.',
        ];
    }
}
