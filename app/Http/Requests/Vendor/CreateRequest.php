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
            'user.profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
            'user.phone' => 'required|string|max:55',
            'user.gender' => 'required|in:male,female',
            'additional_images' => 'nullable|array|max:3',
            'additional_images.*' => 'nullable|mimes:jpeg,png,jpg,webp|max:8096',
            // 'user.role' => 'required|in:vendor',

            // vendor data
            'service_ids' => 'required|array|max:3',
            'service_ids.*' => 'integer|exists:services,id',
            'account_type' => 'required|in:Individual,Company',
            'status' => 'nullable|in:active,inactive',
            'years_experience' => 'required|integer|min:0',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'has_crew' => 'nullable|boolean',
            'has_business_insurance' => 'nullable|boolean',
            'crew_members' => 'nullable|array',
            'crew_members.*' => 'string|max:255', // each crew member

            // location data
            'street_address' => 'required|string|max:255',
            'extra_address' => 'nullable|string|max:255',
            'country' => 'required|string|max:100',
            'city' => 'required|string|max:100',
            'state' => 'required|string|max:100',
            'zip_code' => 'required|string|max:20',
        ];
    }
}
