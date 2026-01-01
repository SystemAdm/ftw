<?php

namespace App\http\requests\settings;

use Illuminate\Foundation\http\FormRequest;

class UpdateAppearanceRequest extends FormRequest
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
            'appearance' => ['required', 'string', 'in:light,dark,system'],
        ];
    }
}
