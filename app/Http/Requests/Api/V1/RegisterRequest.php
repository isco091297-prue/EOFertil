<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    /**
     * Determina si el usuario puede realizar esta petición.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas de validación.
     */
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
                'digits_between:10,13',
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

    /**
     * Mensajes personalizados.
     */
    public function messages(): array
    {
        return [

            'first_name.required' => 'Ingrese sus nombres.',

            'last_name.required' => 'Ingrese sus apellidos.',

            'identification.required' => 'Ingrese su cédula.',

            'identification.digits' => 'La cédula debe contener 10 dígitos.',

            'identification.unique' => 'Esta cédula ya está registrada.',

            'phone.required' => 'Ingrese su celular.',


            'email.email' => 'Ingrese un correo válido.',

            'email.unique' => 'Este correo ya está registrado.',

            'zone_id.required' => 'Seleccione una zona.',

            'zone_id.exists' => 'La zona seleccionada no es válida.',

            'branch_id.required' => 'Seleccione una sucursal.',

            'branch_id.exists' => 'La sucursal seleccionada no es válida.',

            'warehouse_id.required' => 'Seleccione un almacén.',

            'warehouse_id.exists' => 'El almacén seleccionado no es válido.',

            'password.required' => 'Ingrese una contraseña.',

            'password.confirmed' => 'Las contraseñas no coinciden.',

            'privacy_accepted.accepted' => 'Debe aceptar la política de privacidad.',

            'bank.required' => 'Seleccione un banco.',

            'account_type.required' => 'Seleccione el tipo de cuenta.',

            'account_type.in' => 'El tipo de cuenta seleccionado no es válido.',

            'account_number.required' => 'Ingrese el número de cuenta.',
        ];
    }
}
