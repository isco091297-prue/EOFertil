<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreZoneRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'code' => 'required|string|max:20',

            'name' => 'required|string|max:100',

            'description' => 'nullable|string|max:255',

            'is_active' => 'required|boolean',

        ];
    }
}
