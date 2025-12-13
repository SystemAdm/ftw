<?php

namespace App\Http\Requests\Settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function rules(): array
    {
        return [
            'birthday' => ['nullable', 'date'],
            'postal_code' => ['nullable', 'integer', 'exists:postal_codes,postal_code'],
        ];
    }
}
