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
            'birthday_visibility' => ['required', 'string', 'in:birthdate,birthyear,age,off'],
            'postal_code' => ['nullable', 'integer', 'exists:postal_codes,postal_code'],
            'postal_code_visibility' => ['required', 'string', 'in:postalcode,city,municipality,county,country,off'],
            'phone_public' => ['boolean'],
            'email_public' => ['boolean'],
            'name_public' => ['boolean'],
            'about' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
