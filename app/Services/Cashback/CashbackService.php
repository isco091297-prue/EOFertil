<?php

namespace App\Services\Cashback;

use App\Models\CashbackCampaign;
use App\Models\CashbackTransaction;
use App\Models\CampaignUserRanking;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Exception;


class CashbackService
{
    /**
     * Bono por primera factura.
     */
    private const FIRST_INVOICE_BONUS = 5.00;

    /**
     * Generar cashback de una factura.
     *
     * @throws Exception
     */
    public function generate(
        Invoice $invoice
    ): CashbackTransaction {

        return DB::transaction(function () use ($invoice) {

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $invoice->loadMissing([

                'user',

                'cashbackCampaign',

                'branch',

            ]);

            $user = $invoice->user;

            $campaign = $invoice->cashbackCampaign;

            /*
            |--------------------------------------------------------------------------
            | Validaciones
            |--------------------------------------------------------------------------
            */

            $this->validateInvoice(
                $invoice,
                $campaign,
                $user
            );

            /*
            |--------------------------------------------------------------------------
            | Calcular Cashback
            |--------------------------------------------------------------------------
            */

            $cashback = round(

                (

                    $invoice->total_productos_participantes

                    *

                    $campaign->porcentaje

                ) / 100,

                2

            );

            /*
            |--------------------------------------------------------------------------
            | Bono primera factura
            |--------------------------------------------------------------------------
            */

            $firstInvoiceBonus =

                $this->calculateFirstInvoiceBonus(
                    $user
                );

            /*
            |--------------------------------------------------------------------------
            | Actualizar factura
            |--------------------------------------------------------------------------
            */

            $invoice->update([

                'porcentaje_cashback' =>

                $campaign->porcentaje,

                'cashback_generado' =>

                $cashback,

                'estado' =>

                'confirmada',

            ]);

            /*
            |--------------------------------------------------------------------------
            | Actualizar usuario
            |--------------------------------------------------------------------------
            */

            $this->updateUserBalances(

                $user,

                $cashback,

                $firstInvoiceBonus

            );

            /*
            |--------------------------------------------------------------------------
            | Registrar movimientos
            |--------------------------------------------------------------------------
            */

            $transaction =

                $this->createCashbackTransaction(

                    $user,

                    $invoice,

                    $campaign,

                    $cashback,

                    $firstInvoiceBonus

                );

            /*
            |--------------------------------------------------------------------------
            | Actualizar Ranking
            |--------------------------------------------------------------------------
            */

            $this->updateRanking(

                $campaign,

                $invoice,

                $cashback

            );

            return $transaction;
        });
    }
    /**
     * Validaciones.
     */
    private function validateInvoice(
        Invoice $invoice,
        ?CashbackCampaign $campaign,
        ?User $user
    ): void {

        if (! $user) {
            throw new Exception(
                'La factura no tiene usuario.'
            );
        }

        if (! $campaign) {
            throw new Exception(
                'La factura no pertenece a una campaña.'
            );
        }

        if ($invoice->estado === 'anulada') {
            throw new Exception(
                'La factura está anulada.'
            );
        }

        if ($invoice->cashback_generado > 0) {
            throw new Exception(
                'La factura ya generó cashback.'
            );
        }

        if ($invoice->total_productos_participantes <= 0) {
            throw new Exception(
                'No existen productos participantes.'
            );
        }
    }

    /**
     * Bono primera factura.
     */
    private function calculateFirstInvoiceBonus(
        User $user
    ): float {

        $exists = CashbackTransaction::query()

            ->where(
                'user_id',
                $user->id
            )

            ->where(
                'tipo',
                'bonificacion'
            )

            ->exists();

        return $exists
            ? 0
            : self::FIRST_INVOICE_BONUS;
    }

    /**
     * Actualizar saldos.
     */
    private function updateUserBalances(
        User $user,
        float $cashback,
        float $bonus
    ): void {

        $user->increment(

            'cashback_total',

            $cashback + $bonus

        );

        $user->increment(

            'cashback_available',

            $cashback + $bonus

        );

        $user->refresh();
    }

    /**
     * Registrar movimientos.
     */
    private function createCashbackTransaction(

        User $user,

        Invoice $invoice,

        CashbackCampaign $campaign,

        float $cashback,

        float $bonus

    ): CashbackTransaction {

        $transaction = CashbackTransaction::create([

            'user_id' =>

            $user->id,

            'invoice_id' =>

            $invoice->id,

            'cashback_campaign_id' =>

            $campaign->id,

            'tipo' =>

            'factura',

            'movimiento' =>

            'ingreso',

            'valor' =>

            $cashback,

            'saldo_despues' =>

            $user->cashback_available - $bonus,

            'descripcion' =>

            'Cashback generado por la factura '
                . $invoice->numero_factura_original,

        ]);

        if ($bonus > 0) {

            CashbackTransaction::create([

                'user_id' =>

                $user->id,

                'invoice_id' =>

                $invoice->id,

                'cashback_campaign_id' =>

                $campaign->id,

                'tipo' =>

                'bonificacion',

                'movimiento' =>

                'ingreso',

                'valor' =>

                $bonus,

                'saldo_despues' =>

                $user->cashback_available,

                'descripcion' =>

                'Bono por registrar tu primera factura',

            ]);
        }

        return $transaction;
    }

    /**
     * Actualizar ranking.
     */
    private function updateRanking(

        CashbackCampaign $campaign,

        Invoice $invoice,

        float $cashback

    ): void {

        if (

            ! $campaign->ranking_enabled

        ) {

            return;
        }

        $ranking = CampaignUserRanking::firstOrNew([

            'cashback_campaign_id' =>

            $campaign->id,

            'user_id' =>

            $invoice->user_id,

        ]);

        if (! $ranking->exists) {

            $ranking->warehouse_id =
                $invoice->user->warehouse_id;

            $ranking->zone_id =
                $invoice->user->zone_id;

            $ranking->branch_id =
                $invoice->branch_id;

            $ranking->sales_total = 0;

            $ranking->cashback_total = 0;

            $ranking->invoice_count = 0;
        }

        $ranking->sales_total +=
            $invoice->total_productos_participantes;

        $ranking->cashback_total +=
            $cashback;

        $ranking->invoice_count++;

        $ranking->position = null;

        $ranking->save();
    }
}
