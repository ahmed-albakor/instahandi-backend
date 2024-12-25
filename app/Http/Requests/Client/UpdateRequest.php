<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user.first_name' => 'nullable|string|max:255',
            'user.last_name' => 'nullable|string|max:255',
            'user.email' => 'nullable|string|email|max:100|unique:users,email,' . ($this->vendor->user->id ?? 'NULL'),
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'nullable|in:male,female',
            'additional_images' => 'nullable|array|max:3',
            'additional_images.*' => 'nullable|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
