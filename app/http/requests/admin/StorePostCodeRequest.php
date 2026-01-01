<?php

namespace App\http\requests\admin;

use Illuminate\Foundation\http\FormRequest;

class StorePostCodeRequest extends FormRequest
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
            'postal_code' => ['required', 'integer', 'digits_between:3,12', 'unique:postal_codes,postal_code'],
            'city' => ['required', 'string', 'max:255'],
            'state' => ['nullable', 'string', 'max:255'],
            'country' => ['nullable', 'string', 'max:255'],
            'municipality' => ['nullable', 'string', 'max:255'],
        ];
    }
}
