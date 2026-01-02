<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreLocationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'postal_code' => ['required', 'integer', 'exists:postal_codes,postal_code'],
            'name' => ['required', 'string', 'max:255'],
            'active' => ['sometimes', 'boolean'],
            'description' => ['nullable', 'string'],
            'latitude' => ['nullable', 'numeric'],
            'longitude' => ['nullable', 'numeric'],
            'google_maps_url' => ['nullable', 'url', 'max:2048'],
            'images' => ['nullable', 'string'],
            'street_address' => ['nullable', 'string', 'max:255'],
            'street_number' => ['nullable', 'string', 'max:50'],
            'link' => ['nullable', 'url', 'max:2048'],
        ];
    }
}
