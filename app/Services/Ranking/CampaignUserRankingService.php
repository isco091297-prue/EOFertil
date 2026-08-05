<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use Illuminate\Database\Eloquent\Collection;

class CampaignUserRankingService
{
    /**
     * Obtener ranking completo.
     */
    public function getRanking(
        CashbackCampaign $campaign
    ): Collection {

        $query = CampaignUserRanking::query()

            ->with([
                'user',
                'warehouse',
                'zone',
                'branch',
            ])

            ->where(
                'cashback_campaign_id',
                $campaign->id
            );

        if (
            $campaign->campaign_type ===
            'ranking_accumulated'
        ) {

            $query

                ->orderByDesc('sales_total')

                ->orderByDesc('cashback_total');
        } else {

            $query

                ->orderByDesc('cashback_total')

                ->orderByDesc('sales_total');
        }

        return $query

            ->orderBy('invoice_count')

            ->get();
    }

    /**
     * Ranking por almacén.
     */
    public function getWarehouseRanking(
        CashbackCampaign $campaign,
        int $warehouseId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'warehouse_id',
                $warehouseId
            )

            ->values();
    }

    /**
     * Ranking por zona.
     */
    public function getZoneRanking(
        CashbackCampaign $campaign,
        int $zoneId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'zone_id',
                $zoneId
            )

            ->values();
    }

    /**
     * Ranking por sucursal.
     */
    public function getBranchRanking(
        CashbackCampaign $campaign,
        int $branchId
    ): Collection {

        return $this->getRanking($campaign)

            ->where(
                'branch_id',
                $branchId
            )

            ->values();
    }

    /**
     * Buscar participante.
     */
    public function find(
        CashbackCampaign $campaign,
        int $userId
    ): ?CampaignUserRanking {

        return CampaignUserRanking::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'user_id',
                $userId
            )

            ->first();
    }

    /**
     * Obtener ganador.
     */
    public function getWinner(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return $this->getRanking(
            $campaign
        )->first();
    }

    /**
     * Reiniciar ranking.
     */
    public function reset(
        CashbackCampaign $campaign
    ): void {

        CampaignUserRanking::query()

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->delete();
    }
}
