<?php

namespace App\Http\Requests\ServiceRequest;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'payment_type' => 'required|in:flat_rate,hourly_rate',
            'estimated_hours' => 'required|string|max:50',
            'price' => 'required|numeric|min:0',
            'start_date' => 'required|date',
            'completion_date' => 'required|date',
            'service_id' => 'required|exists:services,id,deleted_at,NULL',
            'client_id' => 'required|exists:clients,id,deleted_at,NULL',
            // Location Validator
            'street_address' => 'required|string',
            'exstra_address' => 'nullable|string',
            'country' => 'required|string|max:50',
            'city' => 'required|string|max:50',
            'state' => 'required|string|max:20',
            'zip_code' => 'required|string|max:20',
            // Images Validator
            'images.*' => 'image|mimes:jpeg,png,jpg,webp|max:8096',
        ];
    }
}
