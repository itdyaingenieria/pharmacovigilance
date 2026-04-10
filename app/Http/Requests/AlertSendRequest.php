<?php

namespace App\Http\Requests;

class AlertSendRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_id' => 'required|integer|exists:orders,id',
            'lot_number' => 'required|string|max:50',
            'force' => 'nullable|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
