<?php

namespace App\Http\Requests;

use App\Models\RankingReward;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class StoreRankingRewardRequest extends FormRequest
{
    /**
     * Autorizar.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Reglas.
     */
    public function rules(): array
    {
        return [

            'reward_type_id' => [
                'required',
                'exists:reward_types,id',
            ],

            'posicion' => [
                'required',
                'integer',
                'min:1',
                'max:100',

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {

                    $campaign = $this->route(
                        'cashbackCampaign'
                    );

                    $exists = RankingReward::query()

                        ->where(
                            'cashback_campaign_id',
                            $campaign->id
                        )

                        ->where(
                            'posicion',
                            $value
                        )

                        ->exists();

                    if ($exists) {

                        $fail(
                            'Ya existe un premio para esa posición en esta campaña.'
                        );
                    }
                },
            ],

            'titulo' => [
                'required',
                'string',
                'max:255',
            ],

            'descripcion' => [
                'nullable',
                'string',
            ],

            'valor_referencial' => [
                'nullable',
                'numeric',
                'min:0',
            ],

            'multiplicador' => [
                'nullable',
                'numeric',
                'min:1',
            ],

            'activo' => [
                'required',
                'boolean',
            ],

        ];
    }

    /**
     * Mensajes.
     */
    public function messages(): array
    {
        return [

            'reward_type_id.required' =>
            'Seleccione el tipo de premio.',

            'reward_type_id.exists' =>
            'El tipo de premio seleccionado no existe.',

            'posicion.required' =>
            'Ingrese la posición del ranking.',

            'posicion.integer' =>
            'La posición debe ser numérica.',

            'titulo.required' =>
            'Ingrese el título del premio.',

            'valor_referencial.numeric' =>
            'El valor referencial debe ser numérico.',

            'multiplicador.numeric' =>
            'El multiplicador debe ser numérico.',

            'activo.required' =>
            'Seleccione el estado.',

        ];
    }
}
