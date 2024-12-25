<?php

namespace App\Http\Requests\User;

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
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255|unique:users,email,' . $this->route('id'),
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|in:admin,vendor,client',
            'phone' => 'nullable|string|max:25',
            'description' => 'nullable|string',
            'profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
