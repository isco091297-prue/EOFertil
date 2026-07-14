<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'warehouse_id' => 'required|exists:warehouses,id',

            'code' => 'required|string|max:20',

            'name' => 'required|string|max:100',

            'description' => 'nullable|string|max:255',

            'is_active' => 'required|boolean',

        ];
    }
}
