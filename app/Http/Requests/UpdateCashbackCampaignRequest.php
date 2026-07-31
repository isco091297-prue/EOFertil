<?php

namespace App\Http\Requests;

use App\Models\CashbackCampaign;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateCashbackCampaignRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [

            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'porcentaje' => [
                'required',
                'numeric',
                'min:0.01',
                'max:100',
            ],

            'valor_alerta_factura' => [
                'required',
                'numeric',
                'min:0',
            ],

            'fecha_inicio' => [
                'required',
                'date',
            ],

            'fecha_fin' => [
                'required',
                'date',
                'after_or_equal:fecha_inicio',

                function (string $attribute, mixed $value, Closure $fail) {

                    $inicio = $this->fecha_inicio;
                    $fin = $value;

                    $campaign = $this->route('cashbackCampaign')
                        ?? $this->route('cashback_campaign');

                    $id = $campaign?->id;
                    $existe = CashbackCampaign::where('id', '!=', $id)
                        ->where(function ($query) use ($inicio, $fin) {

                            $query
                                ->whereDate('fecha_inicio', '<=', $fin)
                                ->whereDate('fecha_fin', '>=', $inicio);
                        })->exists();

                    if ($existe) {
                        $fail('Ya existe una campaña que se cruza con ese rango de fechas.');
                    }
                },
            ],

            'activo' => [
                'required',
                'boolean',
            ],
        ];
    }

    public function messages(): array
    {
        return [

            'nombre.required' => 'El nombre de la campaña es obligatorio.',

            'porcentaje.required' => 'Debe ingresar el porcentaje.',
            'porcentaje.numeric' => 'El porcentaje debe ser numérico.',
            'porcentaje.min' => 'El porcentaje debe ser mayor que 0.',
            'porcentaje.max' => 'El porcentaje no puede ser mayor a 100.',

            'valor_alerta_factura.required' => 'Debe ingresar el valor de alerta.',
            'valor_alerta_factura.numeric' => 'El valor de alerta debe ser numérico.',

            'fecha_inicio.required' => 'Seleccione la fecha de inicio.',

            'fecha_fin.required' => 'Seleccione la fecha de fin.',
            'fecha_fin.after_or_equal' => 'La fecha fin debe ser mayor o igual a la fecha de inicio.',
        ];
    }
}
