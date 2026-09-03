<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $user = $this->route('user');

        $roleId = (int) $this->input('role_id');

        // Perchero = 2
        $isPerchero = $roleId === 2;

        // Perchero o Guía
        $needsOrganization = in_array($roleId, [2, 3], true);

        return [

            'role_id' => [
                'required',
                'exists:roles,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | ORGANIZACIÓN
            |--------------------------------------------------------------------------
            | Perchero y Guía necesitan:
            | - Almacén
            | - Zona
            | - Sucursal
            |--------------------------------------------------------------------------
            */

            'warehouse_id' => [
                Rule::requiredIf($needsOrganization),
                'nullable',
                'exists:warehouses,id',
            ],

            'zone_id' => [
                Rule::requiredIf($needsOrganization),
                'nullable',
                'exists:zones,id',
            ],

            'branch_id' => [
                Rule::requiredIf($needsOrganization),
                'nullable',
                'exists:branches,id',
            ],

            /*
            |--------------------------------------------------------------------------
            | DATOS PERSONALES
            |--------------------------------------------------------------------------
            */

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
                'string',
                'min:10',
                'max:20',
                Rule::unique('users', 'identification')
                    ->ignore($user->id),
            ],

            'phone' => [
                'required',
                'string',
                'max:20',
            ],

            /*
            |--------------------------------------------------------------------------
            | ACCESO
            |--------------------------------------------------------------------------
            */

            'username' => [
                'required',
                'string',
                'max:50',
                Rule::unique('users', 'username')
                    ->ignore($user->id),
            ],

            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email')
                    ->ignore($user->id),
            ],

            'password' => [
                'nullable',
                'string',
                'min:8',
                'confirmed',
            ],

            'is_active' => [
                'required',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | DATOS BANCARIOS
            |--------------------------------------------------------------------------
            | SOLO Perchero.
            |--------------------------------------------------------------------------
            */

            'bank' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'string',
                'max:100',
            ],

            'account_type' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'string',
                'max:50',
            ],

            'account_number' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'string',
                'max:50',
            ],

        ];
    }
}
