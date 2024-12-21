<?php

namespace App\Http\Requests\Notification;

use Illuminate\Foundation\Http\FormRequest;

class CreateNotificationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'type' => 'required|in:system,order,payment,custom',
            'title' => 'required|string|max:255',
            'message' => 'required|string',
            'data' => 'nullable|array',
            'creator_id' => 'required|exists:users,id',
            'image' => 'nullable|string|max:255',
            'users' => 'required|array|min:1',
            'users.*' => 'exists:users,id',
        ];
    }
}
