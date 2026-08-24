<?php

namespace App\Http\Requests\Api\V1;

use Illuminate\Foundation\Http\FormRequest;
use Carbon\Carbon;

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
                'exists:cashback_campaigns,id',
            ],

            'numero_factura_original' => [
                'required',
                'digits:6',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $user = $this->user();

                    if (!$user?->branch_id) {
                        $fail('Tu usuario no tiene una sucursal asignada.');
                        return;
                    }

                    $exists = \App\Models\Invoice::query()
                        ->where('branch_id', $user->branch_id)
                        ->where(
                            'numero_factura_normalizado',
                            preg_replace('/\D/', '', (string) $value)
                        )
                        ->exists();

                    if ($exists) {
                        $fail('Esta factura ya fue registrada en tu sucursal.');
                    }
                },
            ],

            'fecha_factura' => [
                'required',
                'date_format:Y-m-d',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    $date = Carbon::createFromFormat('Y-m-d', (string) $value)->startOfDay();
                    $today = now()->startOfDay();
                    $firstDayOfMonth = $today->copy()->startOfMonth();

                    if ($date->lt($firstDayOfMonth) || $date->gt($today)) {
                        $fail(
                            'Solo puedes registrar facturas del mes en curso y no posteriores a hoy.'
                        );
                    }
                },
            ],

            /*
            |--------------------------------------------------------------------------
            | Foto de la factura
            |--------------------------------------------------------------------------
            */

            'foto_factura' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png',
                'max:5120',
            ],

            /*
            |--------------------------------------------------------------------------
            | OCR
            |--------------------------------------------------------------------------
            |
            | Todavía no estamos procesando OCR.
            | Se mantiene preparado para implementarlo posteriormente.
            |
            */

            'ocr_result' => [
                'nullable',
                'array',
            ],

            /*
            |--------------------------------------------------------------------------
            | Productos
            |--------------------------------------------------------------------------
            */

            'items' => [
                'required',
                'array',
                'min:1',
            ],

            'items.*.product_id' => [
                'required',
                'integer',
                'exists:products,id',
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

            'cashback_campaign_id.required' =>
            'Debe seleccionar una campaña.',

            'numero_factura_original.required' =>
            'Debe ingresar el número de factura.',

            'numero_factura_original.digits' =>
            'Debe ingresar únicamente los últimos 6 dígitos de la factura.',

            'fecha_factura.required' =>
            'Debe ingresar la fecha de la factura.',

            'fecha_factura.date_format' =>
            'La fecha de la factura es inválida. Usa el formato YYYY-MM-DD.',

            /*
            |--------------------------------------------------------------------------
            | Foto
            |--------------------------------------------------------------------------
            */

            'foto_factura.required' =>
            'Debe tomar una foto de la factura.',

            'foto_factura.image' =>
            'El archivo seleccionado debe ser una imagen.',

            'foto_factura.mimes' =>
            'La foto de la factura debe ser JPG, JPEG o PNG.',

            'foto_factura.max' =>
            'La foto de la factura no puede superar los 5 MB.',

            /*
            |--------------------------------------------------------------------------
            | Productos
            |--------------------------------------------------------------------------
            */

            'items.required' =>
            'Debe registrar al menos un producto.',

            'items.array' =>
            'Los productos enviados son inválidos.',

            'items.min' =>
            'Debe registrar al menos un producto.',

            'items.*.product_id.required' =>
            'Todos los productos son obligatorios.',

            'items.*.valor.required' =>
            'Debe ingresar el valor del producto.',

            'items.*.valor.numeric' =>
            'El valor del producto es inválido.',

            'items.*.valor.gt' =>
            'El valor del producto debe ser mayor que cero.',

        ];
    }
}
