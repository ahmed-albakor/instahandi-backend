<?php

namespace App\Http\Requests\Vendor;

use Illuminate\Foundation\Http\FormRequest;

class SetupProfileRequest extends FormRequest
{
    protected $stopOnFirstFailure = true;

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
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:50',
            'phone' => 'required|string|max:25',
            'description' => 'nullable|string',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg,webp|max:8096',
            'account_type' => 'required|in:Individual,Company',
            'years_experience' => 'required|integer|min:0',
            'longitude' => 'nullable|string',
            'latitude' => 'nullable|string',
            'has_crew' => 'nullable|boolean',
            'has_business_insurance' => 'nullable|boolean',
            'crew_members' => 'nullable|array',
            'crew_members.*.name' => 'required|string',
            'crew_members.*.title' => 'required|string',
            'service_ids' => 'required|array|max:3',
            'service_ids.*' => 'integer|exists:services,id',
            // Location validator
            'street_address' => 'required|string',
            'exstra_address' => 'nullable|string',
            'country' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            'additional_images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
