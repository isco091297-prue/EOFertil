<?php

namespace App\Services\Cashback;

use App\Models\CashbackCampaign;
use App\Models\CashbackTransaction;
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
            |
            | El cashback se calcula únicamente sobre los productos
            | participantes de la factura.
            |
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
            | IMPORTANTE
            |--------------------------------------------------------------------------
            |
            | El ranking NO se actualiza aquí.
            |
            | El ranking se procesa posteriormente desde:
            |
            |     InvoiceService
            |         ↓
            |     RankingCalculatorService
            |
            | Esto evita que una misma factura se contabilice dos veces.
            |
            | RankingCalculatorService se encarga de:
            |
            |     - Ranking Cashback
            |     - Ranking Acumulado
            |
            */

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

        if (!$user) {
            throw new Exception(
                'La factura no tiene usuario.'
            );
        }

        if (!$campaign) {
            throw new Exception(
                'La factura no pertenece a una campaña.'
            );
        }

        if ($invoice->estado === 'anulada') {
            throw new Exception(
                'La factura está anulada.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Evitar generar cashback dos veces
        |--------------------------------------------------------------------------
        */

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

        /*
        |--------------------------------------------------------------------------
        | Movimiento principal de cashback
        |--------------------------------------------------------------------------
        */

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

            /*
            | El saldo antes del bono corresponde al saldo actual
            | menos el bono que acaba de agregarse.
            */
            'saldo_despues' =>
            $user->cashback_available - $bonus,

            'descripcion' =>
            'Cashback generado por la factura '
                . $invoice->numero_factura_original,
        ]);

        /*
        |--------------------------------------------------------------------------
        | Bono primera factura
        |--------------------------------------------------------------------------
        */

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
}
