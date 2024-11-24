<?php

namespace App\Http\Requests\VendorReview;

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
            'order_id' => 'required|exists:orders,id',
            // 'client_id' => 'required|exists:clients,id',
            'rating' => 'required|integer|min:1|max:5',
            'review' => 'nullable|string',
        ];
    }
}
