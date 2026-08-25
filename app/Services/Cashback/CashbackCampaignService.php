<?php

namespace App\Services\Cashback;

use App\Models\CashbackCampaign;
use App\Models\RankingReward;
use App\Models\RewardType;
use Illuminate\Support\Facades\DB;

class CashbackCampaignService
{
    public function __construct(
        protected CashbackCampaignParticipantService $participantService
    ) {}

    /**
     * Crear campaña.
     */
    public function store(
        array $data
    ): CashbackCampaign {

        return DB::transaction(function () use ($data) {

            $campaignType = $data['campaign_type'];

            /*
            |--------------------------------------------------------------------------
            | Configuración según tipo de campaña
            |--------------------------------------------------------------------------
            |
            | Cashback:
            |   porcentaje = valor configurado
            |   ranking_type = cashback/sales
            |
            | Ranking acumulado:
            |   no utiliza porcentaje
            |   acumulamos el valor de los productos EOFertil
            |   ranking_type = sales
            |
            */

            $isAccumulated =
                $campaignType === 'ranking_accumulated';

            $rankingEnabled =
                ! $isAccumulated &&
                !empty($data['ranking_enabled']);

            $campaign = CashbackCampaign::create([

                'nombre' =>
                $data['nombre'],

                'descripcion' =>
                $data['descripcion'] ?? null,

                'campaign_type' =>
                $campaignType,

                'participant_type' =>
                $data['participant_type'],

                'ranking_enabled' =>
                $rankingEnabled,

                'ranking_type' =>
                $isAccumulated
                    ? 'sales'
                    : ($data['ranking_type'] ?? 'cashback'),

                /*
                |--------------------------------------------------------------------------
                | Porcentaje
                |--------------------------------------------------------------------------
                |
                | El acumulado no utiliza porcentaje.
                | Guardamos 0 porque la columna actualmente no permite NULL.
                |
                */

                'porcentaje' =>
                $isAccumulated
                    ? 0
                    : ($data['porcentaje'] ?? 0),

                'valor_alerta_factura' =>
                $data['valor_alerta_factura'] ?? 0,

                'fecha_inicio' =>
                $data['fecha_inicio'],

                'fecha_fin' =>
                $data['fecha_fin'],

                'activo' =>
                $data['activo'],

            ]);

            /*
            |--------------------------------------------------------------------------
            | Participantes
            |--------------------------------------------------------------------------
            */

            $this->participantService->save(
                $campaign,
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | Premio automático del Ranking Cashback
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            |
            | Esto solamente aplica a Cashback con Ranking Cashback.
            |
            | Una campaña acumulada NO debe crear automáticamente
            | un premio de multiplicador de Cashback.
            |
            */

            if (
                $campaignType === 'cashback' &&
                $rankingEnabled
            ) {

                $rewardType = RewardType::query()
                    ->where(
                        'codigo',
                        'cashback_multiplier'
                    )
                    ->first();

                if ($rewardType) {

                    RankingReward::create([

                        'cashback_campaign_id' =>
                        $campaign->id,

                        'reward_type_id' =>
                        $rewardType->id,

                        'posicion' =>
                        1,

                        'titulo' =>
                        $data['reward_title'],

                        'descripcion' =>
                        $data['reward_description'] ?? null,

                        'valor_referencial' =>
                        $data['reward_value'] ?? null,

                        'multiplicador' =>
                        $data['multiplicador'],

                        'activo' =>
                        true,

                    ]);
                }
            }

            return $campaign;
        });
    }

    /**
     * Actualizar campaña.
     */
    public function update(
        CashbackCampaign $campaign,
        array $data
    ): CashbackCampaign {

        return DB::transaction(function () use (
            $campaign,
            $data
        ) {

            $campaignType =
                $data['campaign_type'];

            $isAccumulated =
                $campaignType === 'ranking_accumulated';

            $rankingEnabled =
                ! $isAccumulated &&
                !empty($data['ranking_enabled']);

            $campaign->update([

                'nombre' =>
                $data['nombre'],

                'descripcion' =>
                $data['descripcion'] ?? null,

                'campaign_type' =>
                $campaignType,

                'participant_type' =>
                $data['participant_type'],

                'ranking_enabled' =>
                $rankingEnabled,

                'ranking_type' =>
                $isAccumulated
                    ? 'sales'
                    : ($data['ranking_type'] ?? 'cashback'),

                /*
                |--------------------------------------------------------------------------
                | Porcentaje
                |--------------------------------------------------------------------------
                */

                'porcentaje' =>
                $isAccumulated
                    ? 0
                    : ($data['porcentaje'] ?? 0),

                'valor_alerta_factura' =>
                $data['valor_alerta_factura'] ?? 0,

                'fecha_inicio' =>
                $data['fecha_inicio'],

                'fecha_fin' =>
                $data['fecha_fin'],

                'activo' =>
                $data['activo'],

            ]);

            /*
            |--------------------------------------------------------------------------
            | Participantes
            |--------------------------------------------------------------------------
            */

            $this->participantService->save(
                $campaign,
                $data
            );

            /*
            |--------------------------------------------------------------------------
            | Ranking Cashback
            |--------------------------------------------------------------------------
            */

            if (
                $campaignType === 'cashback' &&
                $rankingEnabled
            ) {

                $rewardType = RewardType::query()
                    ->where(
                        'codigo',
                        'cashback_multiplier'
                    )
                    ->first();

                if ($rewardType) {

                    RankingReward::updateOrCreate(

                        [
                            'cashback_campaign_id' =>
                            $campaign->id,

                            'posicion' =>
                            1,
                        ],

                        [
                            'reward_type_id' =>
                            $rewardType->id,

                            'titulo' =>
                            $data['reward_title'],

                            'descripcion' =>
                            $data['reward_description'] ?? null,

                            'valor_referencial' =>
                            $data['reward_value'] ?? null,

                            'multiplicador' =>
                            $data['multiplicador'],

                            'activo' =>
                            true,
                        ]
                    );
                }
            } else {

                /*
                |--------------------------------------------------------------------------
                | Si no es Ranking Cashback
                |--------------------------------------------------------------------------
                |
                | Esto también limpia los premios antiguos si una campaña
                | Cashback con ranking se transforma en acumulada.
                |
                */

                $campaign
                    ->rankingRewards()
                    ->delete();
            }

            return $campaign;
        });
    }

    /**
     * Eliminar campaña.
     */
    public function delete(
        CashbackCampaign $campaign
    ): void {

        DB::transaction(function () use (
            $campaign
        ) {

            $campaign
                ->rankingRewards()
                ->delete();

            $campaign
                ->scopes()
                ->delete();

            $campaign->delete();
        });
    }
}
