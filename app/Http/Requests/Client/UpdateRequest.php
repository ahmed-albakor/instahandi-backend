<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;


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
            'email' => 'nullable|email|max:255|unique:users,email',
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'nullable|in:male,female',
            'additional_images' => 'nullable|array|max:3',
            'additional_images.*' => 'nullable|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
