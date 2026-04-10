<?php

namespace App\Http\Requests;

class MedicationSearchRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'lot' => 'required|string|max:50',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'per_page' => 'nullable|integer|min:1|max:100',
            'page' => 'nullable|integer|min:1',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
