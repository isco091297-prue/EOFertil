<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;

class StoreInvoiceRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [

            'cashback_campaign_id' => [
                'required',
                'integer',
            ],

            'numero_factura_original' => [
                'required',
                'digits:6',
            ],

            'fecha_factura' => [
                'required',
                'date',
            ],

            'foto_factura' => [
                'nullable',
                'string',
            ],

            'ocr_result' => [
                'nullable',
                'array',
            ],

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
            ],

            'items.*.valor' => [
                'required',
                'numeric',
                'gt:0',
            ],

        ];
    }

    /**
     * Custom validation messages.
     *
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [

            'cashback_campaign_id.required' => 'Debe seleccionar una campaña.',

            'numero_factura_original.required' => 'Debe ingresar el número de factura.',

            'numero_factura_original.digits' => 'Debe ingresar únicamente los últimos 6 dígitos de la factura.',

            'fecha_factura.required' => 'Debe ingresar la fecha de la factura.',

            'fecha_factura.date' => 'La fecha de la factura es inválida.',

            'items.required' => 'Debe registrar al menos un producto.',

            'items.array' => 'Los productos enviados son inválidos.',

            'items.min' => 'Debe registrar al menos un producto.',

            'items.*.product_id.required' => 'Todos los productos son obligatorios.',

            'items.*.valor.required' => 'Debe ingresar el valor del producto.',

            'items.*.valor.numeric' => 'El valor del producto es inválido.',

            'items.*.valor.gt' => 'El valor del producto debe ser mayor que cero.',

        ];
    }
}
