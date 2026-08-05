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
     * los rankings de las campañas
     * en las que participa.
     */
    public function process(
        Invoice $invoice
    ): void {

        $invoice->loadMissing([
            'user',
            'branch',
        ]);

        /*
        |--------------------------------------------------------------------------
        | Buscar campañas vigentes
        |--------------------------------------------------------------------------
        */

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

            ->whereIn(
                'campaign_type',
                [
                    'cashback',
                    'ranking_accumulated',
                ]
            )

            ->get();

        foreach ($campaigns as $campaign) {

            if (! $this->campaignApplies(
                $campaign,
                $invoice
            )) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ranking Cashback
            |--------------------------------------------------------------------------
            */

            if ($campaign->campaign_type === 'cashback') {

                if (! $campaign->ranking_enabled) {
                    continue;
                }

                $this->campaignUserRankingService
                    ->updateFromInvoice(

                        $campaign,

                        $invoice,

                        (float) $invoice->total_productos_participantes,

                        (float) $invoice->cashback_generado

                    );

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ranking Acumulado
            |--------------------------------------------------------------------------
            */

            if (
                $campaign->campaign_type ===
                'ranking_accumulated'
            ) {

                $this->campaignUserRankingService
                    ->updateFromInvoice(

                        $campaign,

                        $invoice,

                        (float) $invoice->total_productos_participantes,

                        0

                    );
            }
        }
    }

    /**
     * Verifica si la factura
     * pertenece al alcance
     * de la campaña.
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

        $scopeQuery = $campaign->scopes();

        switch ($campaign->participant_type) {

            case 'warehouse':

                return $scopeQuery

                    ->where(
                        'warehouse_id',
                        $invoice->user->warehouse_id
                    )

                    ->exists();

            case 'zone':

                return $scopeQuery

                    ->where(
                        'zone_id',
                        $invoice->user->zone_id
                    )

                    ->exists();

            case 'branch':

                return $scopeQuery

                    ->where(
                        'branch_id',
                        $invoice->branch_id
                    )

                    ->exists();

            default:

                return false;
        }
    }
}
