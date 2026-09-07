<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => [
                'required',
                'string',
                'max:100',
            ],

            'last_name' => [
                'required',
                'string',
                'max:100',
            ],

            'identification' => [
                'required',
                'numeric',
                'digits:10',
                'unique:users,identification',
            ],

            'phone' => [
                'required',
                'numeric',
                'digits:10',
            ],

            'email' => [
                'nullable',
                'email',
                'max:255',
                'unique:users,email',
            ],

            'zone_id' => [
                'required',
                'exists:zones,id',
            ],

            'branch_id' => [
                'required',
                'exists:branches,id',
            ],

            'warehouse_id' => [
                'required',
                'exists:warehouses,id',
            ],

            'password' => [
                'required',
                'confirmed',
                Password::min(8)
                    ->letters()
                    ->mixedCase()
                    ->numbers(),
            ],

            'privacy_accepted' => [
                'accepted',
            ],

            'responsibility_accepted' => [
                'accepted',
            ],

            'bank' => [
                'required',
                'string',
                'max:100',
            ],

            'account_type' => [
                'required',
                'in:ahorros,corriente',
            ],

            'account_number' => [
                'required',
                'string',
                'max:50',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            // Nombres
            'first_name.required' => 'Ingrese sus nombres.',
            'first_name.string' => 'Los nombres no son válidos.',
            'first_name.max' => 'Los nombres no pueden superar los 100 caracteres.',

            // Apellidos
            'last_name.required' => 'Ingrese sus apellidos.',
            'last_name.string' => 'Los apellidos no son válidos.',
            'last_name.max' => 'Los apellidos no pueden superar los 100 caracteres.',

            // Cédula
            'identification.required' => 'Ingrese su cédula.',
            'identification.numeric' => 'La cédula debe contener únicamente números.',
            'identification.digits' => 'La cédula debe contener 10 dígitos.',
            'identification.unique' => 'Esta cédula ya está registrada.',

            // Celular
            'phone.required' => 'Ingrese su celular.',
            'phone.numeric' => 'El celular debe contener únicamente números.',
            'phone.digits' => 'El celular debe contener 10 dígitos.',

            // Correo
            'email.email' => 'Ingrese un correo electrónico válido.',
            'email.max' => 'El correo electrónico no puede superar los 255 caracteres.',
            'email.unique' => 'Este correo electrónico ya está registrado.',

            // Zona
            'zone_id.required' => 'Seleccione una zona.',
            'zone_id.exists' => 'La zona seleccionada no es válida.',

            // Sucursal
            'branch_id.required' => 'Seleccione una sucursal.',
            'branch_id.exists' => 'La sucursal seleccionada no es válida.',

            // Almacén
            'warehouse_id.required' => 'Seleccione un almacén.',
            'warehouse_id.exists' => 'El almacén seleccionado no es válido.',

            // Contraseña
            'password.required' => 'Ingrese una contraseña.',
            'password.confirmed' => 'Las contraseñas no coinciden.',

            // Aceptaciones
            'privacy_accepted.accepted' => 'Debe aceptar la política de privacidad.',
            'responsibility_accepted.accepted' => 'Debe aceptar el acuerdo de responsabilidad.',

            // Banco
            'bank.required' => 'Ingrese el nombre del banco o cooperativa.',
            'bank.string' => 'El banco o cooperativa no es válido.',
            'bank.max' => 'El nombre del banco o cooperativa no puede superar los 100 caracteres.',

            // Tipo de cuenta
            'account_type.required' => 'Seleccione el tipo de cuenta.',
            'account_type.in' => 'El tipo de cuenta seleccionado no es válido.',

            // Número de cuenta
            'account_number.required' => 'Ingrese el número de cuenta.',
            'account_number.string' => 'El número de cuenta no es válido.',
            'account_number.max' => 'El número de cuenta no puede superar los 50 caracteres.',
        ];
    }
}
