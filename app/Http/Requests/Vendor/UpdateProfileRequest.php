<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProfileRequest extends FormRequest
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
            'first_name' => 'string|max:255',
            'last_name' => 'string|max:255',
            'phone' => 'string|max:15',
            'description' => 'nullable|string',
            'street_address' => 'string|max:255',
            'exstra_address' => 'nullable|string|max:255',
            'country' => 'string|max:255',
            'city' => 'string|max:255',
            'state' => 'string|max:255',
            'zip_code' => 'string|max:10',
            'profile_photo' => 'nullable|image|max:2048',
            'additional_images.*' => 'nullable|image|max:2048',
            'account_type' => 'nullable|string|max:50',
            'years_experience' => 'nullable|integer|min:0',
            'longitude' => 'nullable|numeric',
            'latitude' => 'nullable|numeric',
            'has_crew' => 'nullable|boolean',
            'crew_members' => 'nullable|integer|min:1',
            'service_ids' => 'nullable|array',
            'service_ids.*' => 'nullable|integer|exists:services,id',
        ];
    }
}
