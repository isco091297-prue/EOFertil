<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreBranchRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'warehouse_id' => 'required|exists:warehouses,id',

            'zone_id' => 'required|exists:zones,id',

            'code' => 'required|max:20',

            'name' => 'required|max:100',

            'address' => 'nullable|max:255',

            'phone' => 'nullable|max:30',

            'description' => 'nullable|max:255',

            'is_active' => 'required|boolean',

        ];
    }
}
