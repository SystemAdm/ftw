<?php

namespace App\Http\Requests\Admin;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user()->hasRole(\App\Enums\RolesEnum::ADMIN->value) || $this->user()->hasRole(\App\Enums\RolesEnum::OWNER->value);
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'given_name' => 'required|string|max:255',
            'middle_name' => 'nullable|string|max:255',
            'family_name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:\App\Models\User',
            'password' => ['required', \Illuminate\Validation\Rules\Password::defaults()],
            'birthday' => 'required|date',
            'postal_code' => 'required|string|max:20',
        ];
    }
}
