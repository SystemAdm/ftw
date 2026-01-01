<?php

namespace App\http\requests\settings;

use Illuminate\Foundation\Http\FormRequest;

class UpdateHeaderImageRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'header_image' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:8192'],
        ];
    }
}
