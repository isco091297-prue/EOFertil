<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateWarehouseRequest extends FormRequest
{
   public function authorize(): bool
{
    return true;
}

public function rules(): array
{
    $warehouse = $this->route('warehouse');

    return [
        'code' => 'required|string|max:20|unique:warehouses,code,' . $warehouse->id,
        'name' => 'required|string|max:100|unique:warehouses,name,' . $warehouse->id,
        'description' => 'nullable|string|max:255',
        'is_active' => 'required|boolean',
    ];
}
}
