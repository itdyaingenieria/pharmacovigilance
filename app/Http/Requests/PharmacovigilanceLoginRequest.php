<?php

namespace App\Http\Requests;

class PharmacovigilanceLoginRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'username' => 'required|string|max:100',
            'password' => 'required|string|max:255',
        ];
    }

    public function authorize(): bool
    {
        return true;
    }
}
