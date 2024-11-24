<?php

namespace App\Http\Requests\Testimonial;

use Illuminate\Foundation\Http\FormRequest;

class CreateRequest extends FormRequest
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
            'message' => 'required|string',
            'rating' => 'required|integer|between:1,5',
            'client_name' => 'required|string|max:255',
            'job' => 'required|string|max:255',
            'profile_photo' => 'required|image|mimes:jpeg,png,jpg|max:2048',
        ];
    }
}
