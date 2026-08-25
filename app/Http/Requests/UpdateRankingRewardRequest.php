<?php

namespace App\Http\Requests;

use App\Models\RankingReward;
use App\Models\RewardType;
use Closure;
use Illuminate\Foundation\Http\FormRequest;

class UpdateRankingRewardRequest extends FormRequest
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

                /*
                |--------------------------------------------------------------------------
                | Validar tipo de premio según campaña
                |--------------------------------------------------------------------------
                */

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {

                    $campaign = $this->route(
                        'cashbackCampaign'
                    );

                    $rewardType = RewardType::find($value);

                    if (
                        $campaign &&
                        $campaign->campaign_type ===
                        'ranking_accumulated' &&
                        $rewardType &&
                        $rewardType->codigo ===
                        'cashback_multiplier'
                    ) {

                        $fail(
                            'El premio Multiplicador de Cashback no está permitido en una campaña de Ranking Acumulado.'
                        );
                    }
                },
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

                    /*
                    |--------------------------------------------------------------------------
                    | Obtener premio actual
                    |--------------------------------------------------------------------------
                    |
                    | Soportamos ambos nombres porque actualmente la ruta
                    | resource puede generar ranking_reward.
                    |
                    */

                    $rankingReward =
                        $this->route('rankingReward')
                        ?? $this->route('ranking_reward');

                    if (
                        !$campaign ||
                        !$rankingReward
                    ) {
                        return;
                    }

                    /*
                    |--------------------------------------------------------------------------
                    | Obtener ID del premio actual
                    |--------------------------------------------------------------------------
                    */

                    $rewardId = $rankingReward instanceof RankingReward
                        ? $rankingReward->id
                        : (int) $rankingReward;

                    /*
                    |--------------------------------------------------------------------------
                    | Verificar posición duplicada
                    |--------------------------------------------------------------------------
                    */

                    $exists = RankingReward::query()

                        ->where(
                            'cashback_campaign_id',
                            $campaign->id
                        )

                        ->where(
                            'posicion',
                            $value
                        )

                        ->where(
                            'id',
                            '!=',
                            $rewardId
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

                /*
                |--------------------------------------------------------------------------
                | El acumulado nunca usa multiplicador
                |--------------------------------------------------------------------------
                */

                function (
                    string $attribute,
                    mixed $value,
                    Closure $fail
                ) {

                    $campaign = $this->route(
                        'cashbackCampaign'
                    );

                    if (
                        $campaign &&
                        $campaign->campaign_type ===
                        'ranking_accumulated' &&
                        $value !== null &&
                        $value !== ''
                    ) {

                        $fail(
                            'Una campaña de Ranking Acumulado no utiliza multiplicador de Cashback.'
                        );
                    }
                },
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

            'posicion.min' =>
            'La posición debe ser mayor o igual a 1.',

            'posicion.max' =>
            'La posición no puede ser mayor a 100.',

            'titulo.required' =>
            'Ingrese el título del premio.',

            'valor_referencial.numeric' =>
            'El valor referencial debe ser numérico.',

            'valor_referencial.min' =>
            'El valor referencial no puede ser negativo.',

            'multiplicador.numeric' =>
            'El multiplicador debe ser numérico.',

            'multiplicador.min' =>
            'El multiplicador debe ser mayor o igual a 1.',

            'activo.required' =>
            'Seleccione el estado.',

        ];
    }
}
