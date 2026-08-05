<?php

namespace App\Services\Cashback;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use App\Models\CashbackCampaignWinner;
use App\Models\CashbackTransaction;
use App\Models\RankingReward;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class CalculateRankingCashbackService
{
    /**
     * Procesar el ranking de una campaña.
     */
    public function execute(
        CashbackCampaign $campaign
    ): void {

        if (! $campaign->activo) {
            return;
        }

        if (! $campaign->ranking_enabled) {
            return;
        }

        if ($campaign->ranking_processed) {
            return;
        }

        DB::transaction(function () use ($campaign) {

            $this->processRanking($campaign);

            $campaign->update([
                'ranking_processed' => true,
            ]);
        });
    }
    /**
     * Procesar ranking.
     */
    protected function processRanking(
        CashbackCampaign $campaign
    ): void {

        $rewards = RankingReward::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'activo',
                true
            )

            ->orderBy('posicion')

            ->get();

        if ($rewards->isEmpty()) {
            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Ranking General
    |--------------------------------------------------------------------------
    */

        if ($campaign->participant_type === 'all') {

            $this->processGroup(
                $campaign,
                CampaignUserRanking::query()
                    ->where(
                        'cashback_campaign_id',
                        $campaign->id
                    ),
                $rewards
            );

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Ranking por Almacén
    |--------------------------------------------------------------------------
    */

        if ($campaign->participant_type === 'warehouse') {

            $groups = CampaignUserRanking::query()

                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )

                ->select('warehouse_id')

                ->distinct()

                ->pluck('warehouse_id');

            foreach ($groups as $warehouseId) {

                $this->processGroup(

                    $campaign,

                    CampaignUserRanking::query()

                        ->where(
                            'cashback_campaign_id',
                            $campaign->id
                        )

                        ->where(
                            'warehouse_id',
                            $warehouseId
                        ),

                    $rewards

                );
            }

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Ranking por Zona
    |--------------------------------------------------------------------------
    */

        if ($campaign->participant_type === 'zone') {

            $groups = CampaignUserRanking::query()

                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )

                ->select('zone_id')

                ->distinct()

                ->pluck('zone_id');

            foreach ($groups as $zoneId) {

                $this->processGroup(

                    $campaign,

                    CampaignUserRanking::query()

                        ->where(
                            'cashback_campaign_id',
                            $campaign->id
                        )

                        ->where(
                            'zone_id',
                            $zoneId
                        ),

                    $rewards

                );
            }

            return;
        }

        /*
    |--------------------------------------------------------------------------
    | Ranking por Sucursal
    |--------------------------------------------------------------------------
    */

        if ($campaign->participant_type === 'branch') {

            $groups = CampaignUserRanking::query()

                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )

                ->select('branch_id')

                ->distinct()

                ->pluck('branch_id');

            foreach ($groups as $branchId) {

                $this->processGroup(

                    $campaign,

                    CampaignUserRanking::query()

                        ->where(
                            'cashback_campaign_id',
                            $campaign->id
                        )

                        ->where(
                            'branch_id',
                            $branchId
                        ),

                    $rewards

                );
            }
        }
    }
    /**
     * Procesar un grupo del ranking.
     */
    protected function processGroup(
        CashbackCampaign $campaign,
        $query,
        $rewards
    ): void {

        if ($campaign->ranking_type === 'sales') {

            $query

                ->orderByDesc('sales_total')

                ->orderByDesc('cashback_total');
        } else {

            $query

                ->orderByDesc('cashback_total')

                ->orderByDesc('sales_total');
        }

        $query->orderBy('invoice_count');

        $rankings = $query->get();

        foreach ($rewards as $reward) {

            $ranking = $rankings->get(
                $reward->posicion - 1
            );

            if (! $ranking) {
                continue;
            }

            $this->rewardWinner(
                $campaign,
                $ranking,
                $reward
            );
        }
    }
    /**
     * Entregar premio.
     */
    protected function rewardWinner(
        CashbackCampaign $campaign,
        CampaignUserRanking $ranking,
        RankingReward $reward
    ): void {

        $user = User::find(
            $ranking->user_id
        );

        if (! $user) {
            return;
        }

        $bonus = 0;

        if (
            $reward->rewardType?->codigo ===
            'cashback_multiplier'
        ) {

            $cashbackFinal =

                $ranking->cashback_total

                *

                $reward->multiplicador;

            $bonus =

                $cashbackFinal

                -

                $ranking->cashback_total;

            if ($bonus > 0) {

                $user->increment(
                    'cashback_total',
                    $bonus
                );

                $user->increment(
                    'cashback_available',
                    $bonus
                );

                $user->refresh();

                CashbackTransaction::create([

                    'user_id' => $user->id,

                    'invoice_id' => null,

                    'cashback_campaign_id' => $campaign->id,

                    'tipo' => 'ranking_bonus',

                    'movimiento' => 'ingreso',

                    'valor' => $bonus,

                    'saldo_despues' =>
                    $user->cashback_available,

                    'descripcion' =>
                    'Premio Ranking Cashback - '
                        . $campaign->nombre,

                ]);
            }
        }

        $this->saveWinner(
            $campaign,
            $ranking,
            $reward,
            $bonus
        );
    }

    /**
     * Guardar ganador.
     */
    protected function saveWinner(
        CashbackCampaign $campaign,
        CampaignUserRanking $ranking,
        RankingReward $reward,
        float $bonus
    ): void {

        CashbackCampaignWinner::updateOrCreate(

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
                $reward->posicion,

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

                'reward_multiplier' =>
                $reward->multiplicador,

                'processed_at' =>
                now(),

            ]

        );
    }
}
