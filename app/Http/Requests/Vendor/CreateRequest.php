<?php

namespace App\Http\Requests\Vendor;

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
            // user data
            'user.first_name' => 'required|string|max:255',
            'user.last_name' => 'required|string|max:255',
            'user.email' => 'required|string|email|max:100|unique:users,email',
            'user.password' => 'required|string|min:6',
            'user.profile_photo' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8096',
            'user.phone' => 'nullable|string|max:55',
            'user.gender' => 'required|in:male,female',
            // 'user.role' => 'required|in:vendor',

            // vendor data
            'account_type' => 'required|in:Individual,Company',
            'status' => 'nullable|in:active,inactive',
            'years_experience' => 'required|integer|min:0',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'has_crew' => 'nullable|boolean',
            'crew_members' => 'nullable|array',
            'crew_members.*' => 'string|max:255', // each crew member
        ];
    }
}
