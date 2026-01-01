<?php

namespace App\http\requests\admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreWeekdayRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'weekday' => ['required', 'integer', 'between:0,6'],
            'team_id' => ['nullable', 'integer', 'exists:teams,id'],
            'location_id' => ['nullable', 'integer', 'exists:locations,id'],
            'active' => ['boolean'],
            'event_start' => ['nullable', 'date'],
            'event_end' => ['nullable', 'date', 'after_or_equal:event_start'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
        ];
    }
}
