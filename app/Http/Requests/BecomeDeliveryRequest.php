<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BecomeDeliveryRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:50'],
            'email' => ['nullable', 'string', 'email', 'max:255'],
            'vehicle_type' => ['required', 'string', 'max:50', Rule::in(['motorcycle', 'car', 'van'])],
            'vehicle_number' => ['required', 'string', 'max:50'],
            'vehicle_color' => ['required', 'string', 'max:50'],
            'message' => ['nullable', 'string', 'max:1000'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('Name is required.'),
            'phone.required' => __('Phone is required.'),
            'vehicle_type.required' => __('Vehicle type is required.'),
            'vehicle_number.required' => __('Vehicle number is required.'),
            'vehicle_color.required' => __('Vehicle color is required.'),
        ];
    }
}
