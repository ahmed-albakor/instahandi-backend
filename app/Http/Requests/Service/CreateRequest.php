<?php

namespace App\Http\Requests\Service;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'name' => 'required|string|max:255',
            'main_image' => 'required|image|mimes:jpeg,png,jpg,webp,svg|max:8096',
            'description' => 'nullable|string',
            'additional_images.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
