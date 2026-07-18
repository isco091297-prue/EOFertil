<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateProductRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'code' => [

                'required',
                'string',
                'max:20',
                Rule::unique('products', 'code')->ignore($this->product),

            ],

            'name' => [

                'required',
                'string',
                'max:150',

            ],

            'brand_id' => [

                'required',
                'exists:brands,id',

            ],

            'category_id' => [

                'required',
                'exists:categories,id',

            ],

            'description' => [

                'nullable',
                'string',

            ],

            'image' => [

                'nullable',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:2048',

            ],

            'is_active' => [

                'required',
                'boolean',

            ],

        ];
    }
}
