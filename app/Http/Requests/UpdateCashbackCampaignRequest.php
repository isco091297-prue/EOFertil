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

            /*
            |--------------------------------------------------------------------------
            | Información general
            |--------------------------------------------------------------------------
            */

            'nombre' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'campaign_type' => [
                'required',
                'in:cashback,ranking_accumulated',
            ],

            /*
            |--------------------------------------------------------------------------
            | Cashback
            |--------------------------------------------------------------------------
            */

            'porcentaje' => [
                'nullable',
                'numeric',
                'min:0.01',
                'max:100',
                'required_if:campaign_type,cashback',
            ],

            'valor_alerta_factura' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Ranking Cashback
            |--------------------------------------------------------------------------
            */

            'ranking_enabled' => [
                'nullable',
                'boolean',
            ],

            'ranking_type' => [
                'nullable',
                'in:cashback,sales',
            ],

            'multiplicador' => [
                'nullable',
                'numeric',
                'min:2',
                'max:5',
            ],

            'reward_title' => [
                'nullable',
                'string',
                'max:255',
            ],

            'reward_description' => [
                'nullable',
                'string',
            ],

            'reward_value' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            /*
            |--------------------------------------------------------------------------
            | Fechas
            |--------------------------------------------------------------------------
            */

            'fecha_inicio' => [
                'required',
                'date',
            ],

            'fecha_fin' => [

                'required',

                'date',

                'after_or_equal:fecha_inicio',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {

                    $inicio = $this->fecha_inicio;
                    $fin = $value;

                    $campaign =
                        $this->route('cashbackCampaign')
                        ?? $this->route('cashback_campaign');

                    $id = $campaign?->id;

                    $existe = CashbackCampaign::query()

                        ->where('id', '!=', $id)

                        ->where(function ($query) use ($inicio, $fin) {

                            $query
                                ->whereDate('fecha_inicio', '<=', $fin)
                                ->whereDate('fecha_fin', '>=', $inicio);
                        })

                        ->where(
                            'campaign_type',
                            $this->campaign_type
                        )

                        ->exists();

                    if ($existe) {

                        $fail(
                            'Ya existe una campaña del mismo tipo en ese rango de fechas.'
                        );
                    }
                }

            ],

            'activo' => [
                'required',
                'boolean',
            ],

            /*
            |--------------------------------------------------------------------------
            | Participantes
            |--------------------------------------------------------------------------
            */

            'participant_type' => [
                'required',
                'in:all,warehouse,zone,branch',
            ],

            'warehouse_ids' => [
                'nullable',
                'array',
            ],

            'warehouse_ids.*' => [
                'integer',
                'exists:warehouses,id',
            ],

            'zone_ids' => [
                'nullable',
                'array',
            ],

            'zone_ids.*' => [
                'integer',
                'exists:zones,id',
            ],

            'branch_ids' => [
                'nullable',
                'array',
            ],

            'branch_ids.*' => [
                'integer',
                'exists:branches,id',
            ],

        ];
    }

    public function messages(): array
    {
        return [

            'nombre.required' =>
            'El nombre de la campaña es obligatorio.',

            'campaign_type.required' =>
            'Debe seleccionar el tipo de campaña.',

            'campaign_type.in' =>
            'El tipo de campaña no es válido.',

            'participant_type.required' =>
            'Debe indicar quiénes participan.',

            'participant_type.in' =>
            'El tipo de participantes no es válido.',

            'porcentaje.required_if' =>
            'Debe ingresar el porcentaje de cashback.',

            'porcentaje.numeric' =>
            'El porcentaje debe ser numérico.',

            'porcentaje.min' =>
            'El porcentaje debe ser mayor que cero.',

            'porcentaje.max' =>
            'El porcentaje no puede ser mayor a 100.',

            'valor_alerta_factura.numeric' =>
            'El valor de alerta debe ser numérico.',

            'fecha_inicio.required' =>
            'Seleccione la fecha de inicio.',

            'fecha_fin.required' =>
            'Seleccione la fecha de fin.',

            'fecha_fin.after_or_equal' =>
            'La fecha fin debe ser mayor o igual a la fecha de inicio.',

        ];
    }
}
