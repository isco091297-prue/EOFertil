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
        |
        | Una factura puede participar simultáneamente:
        |
        | - En una campaña Cashback.
        | - En una campaña Ranking Acumulado.
        |
        | Cada campaña se procesa de forma independiente.
        |
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

            /*
            |--------------------------------------------------------------------------
            | Verificar si el usuario participa
            |--------------------------------------------------------------------------
            */

            if (!$this->campaignApplies(
                $campaign,
                $invoice
            )) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Ranking Cashback
            |--------------------------------------------------------------------------
            |
            | Para este ranking importa:
            |
            | - Valor vendido.
            | - Cashback generado.
            |
            */

            if ($campaign->campaign_type === 'cashback') {

                if (!$campaign->ranking_enabled) {
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
            |
            | Aquí NO importa el cashback generado.
            |
            | El participante acumula exactamente el valor de los
            | productos EOFertil registrados en sus facturas.
            |
            | Ejemplo:
            |
            | Factura 1 → $500
            | Factura 2 → $1.200
            | Factura 3 → $300
            |
            | Acumulado → $2.000
            |
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
     *
     * Actualmente soportamos:
     *
     * - all
     * - warehouse
     * - zone
     * - branch
     */
    protected function campaignApplies(
        CashbackCampaign $campaign,
        Invoice $invoice
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Todos participan
        |--------------------------------------------------------------------------
        */

        if (
            $campaign->participant_type === 'all'
        ) {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Validar usuario
        |--------------------------------------------------------------------------
        */

        if (!$invoice->user) {
            return false;
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener sucursal / almacén / zona
        |--------------------------------------------------------------------------
        */

        $scopeQuery = $campaign->scopes();

        switch ($campaign->participant_type) {

            /*
            |--------------------------------------------------------------------------
            | Participación por almacén
            |--------------------------------------------------------------------------
            |
            | Todos los usuarios pertenecientes a las sucursales
            | de ese almacén participan.
            |
            */

            case 'warehouse':

                if (!$invoice->user->warehouse_id) {
                    return false;
                }

                return $scopeQuery
                    ->where(
                        'warehouse_id',
                        $invoice->user->warehouse_id
                    )
                    ->where(
                        'required',
                        true
                    )
                    ->exists();

                /*
            |--------------------------------------------------------------------------
            | Participación por zona
            |--------------------------------------------------------------------------
            */

            case 'zone':

                if (!$invoice->user->zone_id) {
                    return false;
                }

                return $scopeQuery
                    ->where(
                        'zone_id',
                        $invoice->user->zone_id
                    )
                    ->where(
                        'required',
                        true
                    )
                    ->exists();

                /*
            |--------------------------------------------------------------------------
            | Participación por sucursal
            |--------------------------------------------------------------------------
            */

            case 'branch':

                return $scopeQuery
                    ->where(
                        'branch_id',
                        $invoice->branch_id
                    )
                    ->where(
                        'required',
                        true
                    )
                    ->exists();

                /*
            |--------------------------------------------------------------------------
            | Tipo desconocido
            |--------------------------------------------------------------------------
            */

            default:

                return false;
        }
    }
}
