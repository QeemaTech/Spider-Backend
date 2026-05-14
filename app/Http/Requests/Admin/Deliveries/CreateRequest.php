<?php

namespace App\Http\Requests\Admin\Deliveries;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CreateRequest extends FormRequest
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
        $userType = $this->input('user_type', 'new');

        $rules = [
            'vehicle_type' => ['required', 'string', 'max:50'],
            'vehicle_number' => ['required', 'string', 'max:50'],
            'vehicle_color' => ['required', 'string', 'max:50'],
            'user_type' => ['nullable', 'string', Rule::in(['new', 'existing'])],
            'zone_ids' => ['nullable', 'array'],
            'zone_ids.*' => ['integer', 'exists:zones,id'],
            'is_active' => ['nullable', 'boolean'],
        ];

        if ($userType === 'new') {
            $rules['new_user_name'] = ['required', 'string', 'max:255'];
            $rules['new_user_email'] = ['required', 'string', 'email', 'max:255', 'unique:users,email'];
            $rules['new_user_phone'] = ['nullable', 'string', 'max:50'];
            $rules['new_user_password'] = ['required', 'string', 'min:8', 'confirmed'];
        } else {
            $rules['user_id'] = ['required', 'integer', 'exists:users,id'];
        }

        return $rules;
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'vehicle_type.required' => __('Vehicle type is required.'),
            'vehicle_number.required' => __('Vehicle number is required.'),
            'vehicle_color.required' => __('Vehicle color is required.'),
            'new_user_name.required' => __('Name is required for the new user.'),
            'new_user_email.required' => __('Email is required for the new user.'),
            'new_user_email.unique' => __('This email is already registered.'),
            'new_user_password.required' => __('Password is required for the new user.'),
            'new_user_password.min' => __('Password must be at least 8 characters.'),
            'new_user_password.confirmed' => __('Password confirmation does not match.'),
            'user_id.required' => __('Please select a user to link.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
            'user_type' => $this->input('user_type', 'new'),
        ]);
    }
}
