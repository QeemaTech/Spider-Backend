<?php

namespace App\Http\Requests\Admin\Shifts;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
        $shift = $this->route('shift');
        $currentCount = $shift ? $shift->deliveries()->count() : 0;

        return [
            'date' => ['required', 'date'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'capacity' => ['required', 'integer', 'min:'.$currentCount, 'max:100'],
            'name' => ['nullable', 'string', 'max:100'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'date.required' => __('Shift date is required.'),
            'start_time.required' => __('Start time is required.'),
            'end_time.required' => __('End time is required.'),
            'end_time.after' => __('End time must be after start time.'),
            'capacity.required' => __('Capacity is required.'),
            'capacity.min' => __('Capacity cannot be less than the number of deliveries already assigned.'),
        ];
    }
}
