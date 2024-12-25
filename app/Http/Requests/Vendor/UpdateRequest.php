<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // user data
            'user.first_name' => 'nullable|string|max:255',
            'user.last_name' => 'nullable|string|max:255',
            'user.email' => 'nullable|string|email|max:100|unique:users,email,' . $this->route('id'),
            'user.password' => 'nullable|string|min:6',
            'user.profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'nullable|in:male,female',

            // vendor data
            'account_type' => 'nullable|in:Individual,Company',
            'status' => 'nullable|in:active,inactive',
            'years_experience' => 'nullable|integer|min:0',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'has_crew' => 'nullable|boolean',
            'crew_members' => 'nullable|array',
            'crew_members.*' => 'string|max:255', // each crew member
        ];
    }
}
