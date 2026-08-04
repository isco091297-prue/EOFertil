<?php

namespace App\Services\Ranking;

use App\Models\CampaignUserRanking;
use App\Models\CashbackCampaign;
use App\Models\Invoice;
use Illuminate\Database\Eloquent\Collection;

class CampaignUserRankingService
{
    /**
     * Actualiza el acumulado de un usuario
     * dentro de una campaña.
     */
    public function updateFromInvoice(
        CashbackCampaign $campaign,
        Invoice $invoice,
        float $salesTotal,
        float $cashbackTotal
    ): CampaignUserRanking {

        $invoice->loadMissing([
            'user',
        ]);

        $ranking = CampaignUserRanking::firstOrNew([

            'cashback_campaign_id' => $campaign->id,

            'user_id' => $invoice->user_id,

        ]);

        if (! $ranking->exists) {

            $ranking->warehouse_id = $invoice->user->warehouse_id;

            $ranking->zone_id = $invoice->user->zone_id;

            $ranking->branch_id = $invoice->branch_id;

            $ranking->sales_total = 0;

            $ranking->cashback_total = 0;

            $ranking->invoice_count = 0;
        }

        $ranking->sales_total += $salesTotal;

        $ranking->cashback_total += $cashbackTotal;

        $ranking->invoice_count++;

        $ranking->save();

        return $ranking;
    }

    /**
     * Ranking general.
     */
    public function getRanking(
        CashbackCampaign $campaign
    ): Collection {

        return CampaignUserRanking::query()

            ->with([
                'user',
                'warehouse',
                'zone',
                'branch',
            ])

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->orderByDesc('cashback_total')

            ->orderByDesc('sales_total')

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

        return CampaignUserRanking::query()

            ->with('user')

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'warehouse_id',
                $warehouseId
            )

            ->orderByDesc('cashback_total')

            ->orderByDesc('sales_total')

            ->get();
    }

    /**
     * Ranking por zona.
     */
    public function getZoneRanking(
        CashbackCampaign $campaign,
        int $zoneId
    ): Collection {

        return CampaignUserRanking::query()

            ->with('user')

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'zone_id',
                $zoneId
            )

            ->orderByDesc('cashback_total')

            ->orderByDesc('sales_total')

            ->get();
    }

    /**
     * Ranking por sucursal.
     */
    public function getBranchRanking(
        CashbackCampaign $campaign,
        int $branchId
    ): Collection {

        return CampaignUserRanking::query()

            ->with('user')

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->where(
                'branch_id',
                $branchId
            )

            ->orderByDesc('cashback_total')

            ->orderByDesc('sales_total')

            ->get();
    }

    /**
     * Participante.
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
     * Ganador.
     */
    public function getWinner(
        CashbackCampaign $campaign
    ): ?CampaignUserRanking {

        return CampaignUserRanking::query()

            ->with('user')

            ->where(
                'cashback_campaign_id',
                $campaign->id
            )

            ->orderByDesc('cashback_total')

            ->orderByDesc('sales_total')

            ->first();
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
