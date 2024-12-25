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
            'user.email' => 'nullable|string|email|max:100|unique:users,email,' . $this->route('id'),
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'nullable|in:male,female',
        ];
    }
}
