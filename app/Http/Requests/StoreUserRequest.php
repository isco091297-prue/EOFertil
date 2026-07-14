<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $isPerchero = (int) $this->input('role_id') === 2;
        return [

            'role_id' => 'required|exists:roles,id',

            'warehouse_id' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'exists:warehouses,id',
            ],

            'zone_id' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'exists:zones,id',
            ],

            'branch_id' => [
                Rule::requiredIf($isPerchero),
                'nullable',
                'exists:branches,id',
            ],

            'first_name' => 'required|string|max:100',

            'last_name' => 'required|string|max:100',

            'identification' => 'required|string|min:10|max:20|unique:users,identification',
            'phone' => 'required|string|max:20',
            'username' => 'required|string|max:50|unique:users,username',
            'email' => [
                'nullable',
                'email',
                'max:150',
                Rule::unique('users', 'email'),
            ],

            'password' => 'required|string|min:8|confirmed',

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

            'is_active' => 'required|boolean',

        ];
    }
}
