<?php

namespace App\http\requests\settings;

use App\enums\BirthdayVisibility;
use App\enums\PostalCodeVisibility;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Enum;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'birthday' => ['nullable', 'date'],
            'birthday_visibility' => ['required', new Enum(BirthdayVisibility::class)],
            'postal_code' => ['nullable', 'integer', 'exists:postal_codes,postal_code'],
            'postal_code_visibility' => ['required', new Enum(PostalCodeVisibility::class)],
            'phone_public' => ['boolean'],
            'email_public' => ['boolean'],
            'name_public' => ['boolean'],
            'about' => ['nullable', 'string', 'max:1000'],
        ];
    }
}
