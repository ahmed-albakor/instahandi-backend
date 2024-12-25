<?php

namespace App\Http\Requests\Client;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:100|unique:users,email',
            'user.password' => 'required|string|min:6',
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'required|in:male,female',
        ];
    }
}
