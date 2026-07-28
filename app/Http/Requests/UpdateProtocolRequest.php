<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateProtocolRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'crop_id' => [
                'required',
                'exists:crops,id',
            ],

            'problem_id' => [
                'required',
                'exists:problems,id',
            ],

            'applications' => [
                'required',
                'array',
                'min:1',
            ],

            'applications.*.application_number' => [
                'required',
                'integer',
                'min:1',
            ],

            'applications.*.description' => [
                'nullable',
                'string',
            ],

            'applications.*.products' => [
                'required',
                'array',
                'min:1',
            ],

            'applications.*.products.*.product_id' => [
                'required',
                'exists:products,id',
            ],

            'applications.*.products.*.dose' => [
                'required',
                'numeric',
                'gt:0',
            ],

            'applications.*.products.*.observations' => [
                'nullable',
                'string',
            ],
            'applications.*.application_type' => [
                'nullable',
                'string',
                'max:100',
            ],

        ];
    }
}
