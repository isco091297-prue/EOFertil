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

            $campaign = CashbackCampaign::create([

                'nombre' => $data['nombre'],

                'descripcion' => $data['descripcion'] ?? null,

                'campaign_type' => $data['campaign_type'],

                'participant_type' => $data['participant_type'],

                'ranking_enabled' =>
                !empty($data['ranking_enabled']),

                'ranking_type' =>
                $data['ranking_type'] ?? 'cashback',

                'porcentaje' =>
                $data['porcentaje'] ?? null,

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

            if (!empty($data['ranking_enabled'])) {

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

                        'posicion' => 1,

                        'titulo' =>
                        $data['reward_title'],

                        'descripcion' =>
                        $data['reward_description'] ?? null,

                        'valor_referencial' =>
                        $data['reward_value'] ?? null,

                        'multiplicador' =>
                        $data['multiplicador'],

                        'activo' => true,

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

            $campaign->update([

                'nombre' => $data['nombre'],

                'descripcion' => $data['descripcion'] ?? null,

                'campaign_type' => $data['campaign_type'],

                'participant_type' => $data['participant_type'],

                'ranking_enabled' =>
                !empty($data['ranking_enabled']),

                'ranking_type' =>
                $data['ranking_type'] ?? 'cashback',

                'porcentaje' =>
                $data['porcentaje'] ?? null,

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

            if (!empty($data['ranking_enabled'])) {

                $rewardType = RewardType::query()

                    ->where(
                        'codigo',
                        'cashback_multiplier'
                    )

                    ->first();

                if ($rewardType) {

                    RankingReward::updateOrCreate(

                        [
                            'cashback_campaign_id' => $campaign->id,
                            'posicion' => 1,
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

                            'activo' => true,
                        ]

                    );
                }
            } else {

                /*
                |--------------------------------------------------------------------------
                | Si el ranking fue desactivado,
                | eliminar el premio asociado.
                |--------------------------------------------------------------------------
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
