<?php

namespace App\http\requests\admin;

use Illuminate\Foundation\http\FormRequest;

class StoreEventRequest extends FormRequest
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
            'title' => ['required', 'string', 'max:255'],
            'excerpt' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'image' => ['nullable', 'image', 'max:2048'],
            'image_path' => ['nullable', 'string', 'max:255'],
            'location_id' => ['nullable', 'exists:locations,id'],
            'event_start' => ['required', 'date'],
            'event_end' => ['required', 'date', 'after:event_start'],
            'signup_needed' => ['boolean'],
            'signup_start' => ['nullable', 'required_if:signup_needed,true', 'date'],
            'signup_end' => ['nullable', 'required_if:signup_needed,true', 'date', 'after:signup_start', 'before:event_start'],
            'age_min' => ['nullable', 'integer', 'min:0'],
            'age_max' => ['nullable', 'integer', 'min:0', 'gte:age_min'],
            'number_of_seats' => ['nullable', 'integer', 'min:-1'],
            'status' => ['required', 'string', 'in:draft,published,cancelled'],
        ];
    }
}
