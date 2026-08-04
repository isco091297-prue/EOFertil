<?php

namespace App\Services\Ranking;

use App\Models\CashbackCampaign;
use App\Models\Invoice;

class RankingCalculatorService
{
    public function __construct(
        protected CampaignUserRankingService $campaignUserRankingService
    ) {}

    /**
     * Procesa una factura y actualiza
     * los rankings correspondientes.
     */
    public function process(
        Invoice $invoice
    ): void {

        $invoice->loadMissing([
            'cashbackCampaign',
            'user',
        ]);

        $campaigns = CashbackCampaign::query()

            ->where('activo', true)

            ->whereDate(
                'fecha_inicio',
                '<=',
                $invoice->fecha_factura
            )

            ->whereDate(
                'fecha_fin',
                '>=',
                $invoice->fecha_factura
            )

            ->get();

        foreach ($campaigns as $campaign) {

            if (! $this->campaignApplies(
                $campaign,
                $invoice
            )) {
                continue;
            }

            switch ($campaign->campaign_type) {

                case 'cashback':

                    $this->processCashbackCampaign(
                        $campaign,
                        $invoice
                    );

                    break;

                case 'ranking_cashback':

                    $this->processRankingCashbackCampaign(
                        $campaign,
                        $invoice
                    );

                    break;

                case 'ranking_accumulated':

                    $this->processRankingAccumulatedCampaign(
                        $campaign,
                        $invoice
                    );

                    break;
            }
        }
    }

    /**
     * Verifica si la factura
     * participa en la campaña.
     */
    protected function campaignApplies(
        CashbackCampaign $campaign,
        Invoice $invoice
    ): bool {

        if (
            $campaign->participant_type === 'all'
        ) {

            return true;
        }

        return match ($campaign->participant_type) {

            'warehouse'
            => $campaign->scopes()

                ->where(
                    'scope_type',
                    'warehouse'
                )

                ->where(
                    'scope_id',
                    $invoice->user->warehouse_id
                )

                ->exists(),

            'zone'
            => $campaign->scopes()

                ->where(
                    'scope_type',
                    'zone'
                )

                ->where(
                    'scope_id',
                    $invoice->user->zone_id
                )

                ->exists(),

            'branch'
            => $campaign->scopes()

                ->where(
                    'scope_type',
                    'branch'
                )

                ->where(
                    'scope_id',
                    $invoice->branch_id
                )

                ->exists(),

            default => false,
        };
    }

    /**
     * Cashback normal.
     */
    protected function processCashbackCampaign(
        CashbackCampaign $campaign,
        Invoice $invoice
    ): void {

        // Las campañas normales no generan ranking.

    }

    /**
     * Ranking por cashback.
     */
    protected function processRankingCashbackCampaign(
        CashbackCampaign $campaign,
        Invoice $invoice
    ): void {

        $this->campaignUserRankingService
            ->updateFromInvoice(

                $campaign,

                $invoice,

                (float) $invoice->total_productos_participantes,

                (float) $invoice->cashback_generado

            );
    }

    /**
     * Ranking por compras acumuladas.
     */
    protected function processRankingAccumulatedCampaign(
        CashbackCampaign $campaign,
        Invoice $invoice
    ): void {

        $this->campaignUserRankingService
            ->updateFromInvoice(

                $campaign,

                $invoice,

                (float) $invoice->total_productos_participantes,

                0

            );
    }
}
