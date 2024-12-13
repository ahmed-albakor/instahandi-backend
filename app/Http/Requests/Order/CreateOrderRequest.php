<?php

namespace App\Http\Requests\Order;

use Illuminate\Foundation\Http\FormRequest;

class CreateOrderRequest extends FormRequest
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
            'service_request_id' => 'required|exists:service_requests,id,deleted_at,NULL',
            'vendor_id' => 'required|exists:vendors,id,deleted_at,NULL',
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'status' => 'required|in:pending,execute,completed,canceled',
            'price' => 'required|numeric|min:0',
            'payment_type' => 'required|in:flat_rate,hourly_rate',
            'estimated_hours' => 'required_if:payment_type,hourly_rate|string|max:50',
            'works_hours' => 'nullable|integer',
            'start_date' => 'nullable|date',
            'completion_date' => 'nullable|date',
        ];
    }
}
