<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePlateRequest extends FormRequest
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
            'number' => 'required|unique:plates|string|max:255',
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
            'owner_name.string' => 'Owner name must be a string.',
            'owner_name.max' => 'Owner name cannot exceed 255 characters.',
            'vehicle_type.required' => 'Vehicle type is required.',
            'vehicle_type.string' => 'Vehicle type must be a string.',
            'vehicle_type.max' => 'Vehicle type cannot exceed 255 characters.',
        ];
    }
}
