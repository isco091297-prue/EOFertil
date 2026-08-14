<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreCashbackRedemptionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'monto' => [
                'required',
                'numeric',
                'gt:0',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'monto.required' =>
            'Debe ingresar el monto que desea canjear.',

            'monto.numeric' =>
            'El monto del canje no es válido.',

            'monto.gt' =>
            'El monto del canje debe ser mayor a cero.',
        ];
    }
}
