<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class UpdateRequest extends FormRequest
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
            'title' => 'nullable|string|max:255',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,accepted,completed,rejected,canceled',
            'payment_type' => 'nullable|in:flat_rate,hourly_rate',
            'estimated_hours' => 'nullable|string|max:50',
            'price' => 'nullable|numeric|min:0',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
            // Location Validator
            'street_address' => 'nullable|sometimes|string',
            'exstra_address' => 'nullable|string',
            'country' => 'nullable|string|max:50',
            'city' => 'nullable|string|max:50',
            'state' => 'nullable|string|max:20',
            'zip_code' => 'nullable|string|max:20',
            // Images Validator
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
