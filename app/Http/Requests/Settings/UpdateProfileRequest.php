<?php

namespace App\Http\Requests\Settings;

use App\Enums\BirthdayVisibility;
use App\Enums\PostalCodeVisibility;
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
            'given_name' => ['required', 'string', 'max:255'],
            'middle_name' => ['nullable', 'string', 'max:255'],
            'family_name' => ['required', 'string', 'max:255'],
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
