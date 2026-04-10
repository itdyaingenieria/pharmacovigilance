<?php

namespace App\Http\Requests;

class BulkAlertSendRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'order_ids' => 'required|array|min:1',
            'order_ids.*' => 'integer|distinct|exists:orders,id',
            'lot_number' => 'required|string|max:50',
            'force' => 'nullable|boolean',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
