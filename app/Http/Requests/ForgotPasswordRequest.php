<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ForgotPasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'identification' => [
                'required',
                'string',
                'digits:10',
            ],

            'phone_last4' => [
                'required',
                'string',
                'digits:4',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'identification.required' => 'Ingresa tu número de cédula.',
            'identification.digits' => 'La cédula debe tener 10 dígitos.',

            'phone_last4.required' => 'Ingresa los últimos 4 dígitos de tu teléfono.',
            'phone_last4.digits' => 'Los últimos 4 dígitos deben tener 4 números.',
        ];
    }
}
