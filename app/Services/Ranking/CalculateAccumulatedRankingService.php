<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use App\Models\CashbackCampaignWinner;
use App\Models\RankingReward;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class CalculateAccumulatedRankingService
{
    /**
     * Procesar el ranking acumulado de una campaña.
     *
     * Este servicio es EXCLUSIVO para:
     *
     *     campaign_type = ranking_accumulated
     *
     * No genera, modifica ni entrega Cashback.
     */
    public function execute(
        CashbackCampaign $campaign
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Validar tipo de campaña
        |--------------------------------------------------------------------------
        */

        if (
            $campaign->campaign_type !==
            'ranking_accumulated'
        ) {
            throw new RuntimeException(
                'La campaña indicada no es una campaña de ranking acumulado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar procesamiento duplicado
        |--------------------------------------------------------------------------
        */

        if ($campaign->ranking_processed) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Verificar que la campaña haya terminado
        |--------------------------------------------------------------------------
        */

        if (
            now()->startOfDay()->lte(
                $campaign->fecha_fin
            )
        ) {
            throw new RuntimeException(
                'La campaña de ranking acumulado todavía no ha finalizado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Procesar dentro de una transacción
        |--------------------------------------------------------------------------
        */

        DB::transaction(function () use ($campaign) {

            $this->processRanking($campaign);

            /*
            |--------------------------------------------------------------------------
            | Marcar campaña como procesada
            |--------------------------------------------------------------------------
            */

            $campaign->update([
                'ranking_processed' => true,
            ]);
        });
    }

    /**
     * Procesar el ranking acumulado.
     */
    protected function processRanking(
        CashbackCampaign $campaign
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Obtener participantes
        |--------------------------------------------------------------------------
        |
        | CampaignUserRanking contiene los acumulados generados
        | durante la campaña.
        |
        */

        $rankings = CampaignUserRanking::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->orderByDesc(
                'sales_total'
            )

            ->orderBy(
                'user_id'
            )

            ->get();

        if ($rankings->isEmpty()) {

            throw new RuntimeException(
                'La campaña no tiene participantes con ventas acumuladas.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Asignar posiciones
        |--------------------------------------------------------------------------
        |
        | 1 = mayor acumulado
        | 2 = segundo mayor acumulado
        | 3 = tercer mayor acumulado
        | etc.
        |
        */

        $this->assignPositions(
            $rankings
        );

        /*
        |--------------------------------------------------------------------------
        | Obtener todos los premios activos
        |--------------------------------------------------------------------------
        */

        $rewards = RankingReward::query()

            ->with('rewardType')

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'activo',
                true
            )

            ->orderBy(
                'posicion'
            )

            ->get();

        if ($rewards->isEmpty()) {

            throw new RuntimeException(
                'La campaña no tiene premios activos configurados.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar premios
        |--------------------------------------------------------------------------
        |
        | Una campaña de Ranking Acumulado no puede utilizar
        | premios de multiplicador de Cashback.
        |
        */

        foreach ($rewards as $reward) {

            if (
                $reward->rewardType?->codigo ===
                'cashback_multiplier'
            ) {

                throw new RuntimeException(
                    'La campaña de Ranking Acumulado no puede utilizar premios Multiplicador de Cashback.'
                );
            }
        }

        /*
        |--------------------------------------------------------------------------
        | Procesar cada posición que tenga premio
        |--------------------------------------------------------------------------
        */

        foreach ($rewards as $reward) {

            /*
            |--------------------------------------------------------------------------
            | Obtener participante de esa posición
            |--------------------------------------------------------------------------
            */

            $ranking = $rankings->first(
                function (
                    CampaignUserRanking $ranking
                ) use (
                    $reward
                ) {

                    return (int) $ranking->position ===
                        (int) $reward->posicion;
                }
            );

            /*
            |--------------------------------------------------------------------------
            | Si no existe participante para esa posición,
            | simplemente no se genera ganador.
            |--------------------------------------------------------------------------
            */

            if (!$ranking) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar empate
            |--------------------------------------------------------------------------
            |
            | Si dos participantes tienen exactamente el mismo
            | acumulado, no procesamos automáticamente esa posición.
            |
            */

            $this->validateTie(
                $rankings,
                $ranking
            );

            /*
            |--------------------------------------------------------------------------
            | Guardar ganador
            |--------------------------------------------------------------------------
            */

            $this->saveWinner(
                $campaign,
                $ranking,
                $reward
            );
        }
    }

    /**
     * Asignar posiciones al ranking.
     */
    protected function assignPositions(
        $rankings
    ): void {

        $position = 1;

        foreach ($rankings as $ranking) {

            $ranking->position = $position;

            $ranking->save();

            $position++;
        }
    }

    /**
     * Verificar si existe empate para una posición.
     *
     * No se bloquea toda la campaña por empates posteriores.
     * Solamente se evita asignar automáticamente un premio
     * cuando la posición correspondiente tiene el mismo
     * acumulado que otro participante.
     */
    protected function validateTie(
        $rankings,
        CampaignUserRanking $ranking
    ): void {

        $salesTotal = (float) $ranking->sales_total;

        $sameSales = $rankings->filter(
            function (
                CampaignUserRanking $item
            ) use (
                $salesTotal
            ) {

                return (float) $item->sales_total ===
                    $salesTotal;
            }
        );

        if ($sameSales->count() > 1) {

            throw new RuntimeException(
                'Existe un empate en la posición '
                    . $ranking->position
                    . '. '
                    . 'La campaña no fue procesada hasta definir la regla de desempate.'
            );
        }
    }

    /**
     * Guardar ganador.
     *
     * No genera Cashback.
     */
    protected function saveWinner(
        CashbackCampaign $campaign,
        CampaignUserRanking $ranking,
        RankingReward $reward
    ): CashbackCampaignWinner {

        return CashbackCampaignWinner::updateOrCreate(

            [
                'cashback_campaign_id' =>
                $campaign->id,

                'user_id' =>
                $ranking->user_id,
            ],

            [
                'warehouse_id' =>
                $ranking->warehouse_id,

                'zone_id' =>
                $ranking->zone_id,

                'branch_id' =>
                $ranking->branch_id,

                'ranking_position' =>
                $ranking->position,

                'sales_total' =>
                $ranking->sales_total,

                'cashback_total' =>
                $ranking->cashback_total,

                'reward_type_id' =>
                $reward->reward_type_id,

                'ranking_reward_id' =>
                $reward->id,

                'reward_title' =>
                $reward->titulo,

                'reward_value' =>
                $reward->valor_referencial,

                /*
                |--------------------------------------------------------------------------
                | Ranking Acumulado
                |--------------------------------------------------------------------------
                |
                | Nunca entrega multiplicador de Cashback.
                |
                */

                'reward_multiplier' =>
                null,

                'processed_at' =>
                now(),

            ]
        );
    }
}
