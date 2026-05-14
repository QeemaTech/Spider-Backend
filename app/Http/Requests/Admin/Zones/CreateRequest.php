<?php

namespace App\Http\Requests\Admin\Zones;

use Illuminate\Foundation\Http\FormRequest;

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
        return [
            'name' => ['required', 'string', 'max:255'],
            'polygon' => ['required', 'string', function (string $attribute, string $value, \Closure $fail) {
                $decoded = json_decode($value, true);
                if (! is_array($decoded) || count($decoded) < 3) {
                    $fail(__('The zone boundary must have at least 3 points. Please draw a polygon on the map.'));
                }
            }],
            'is_active' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'name.required' => __('The zone name is required.'),
            'name.max' => __('The zone name must not exceed 255 characters.'),
            'polygon.required' => __('Please draw the zone boundary on the map.'),
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'is_active' => $this->has('is_active') ? (bool) $this->is_active : true,
        ]);
    }
}
