<?php

namespace App\Http\Requests\Proposal;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class PlaceBidRequest extends FormRequest
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
            'vendor_id' => $this->user()->role === 'vendor' ? 'nullable' : 'required|exists:vendors,id,deleted_at,NULL',
        ];
    }
}
