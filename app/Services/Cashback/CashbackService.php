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
            |     InvoiceAdminService
            |         ↓
            |     RankingCalculatorService
            |
            | Esto evita que una misma factura se contabilice dos veces.
            |
            */

            return $transaction;
        });
    }

    /**
     * Revertir los efectos de cashback de una factura.
     *
     * IMPORTANTE:
     *
     * - NO elimina las transacciones originales.
     * - NO elimina retiros existentes.
     * - Crea nuevos movimientos de egreso.
     * - Revierte cashback_total.
     * - Revierte cashback_available.
     * - NO modifica cashback_claimed.
     *
     * Esto permite mantener un historial financiero completo.
     *
     * @throws Exception
     */
    public function reverseInvoice(
        Invoice $invoice,
        ?string $motivo = null
    ): void {

        DB::transaction(function () use (
            $invoice,
            $motivo
        ) {

            /*
            |--------------------------------------------------------------------------
            | Bloquear factura
            |--------------------------------------------------------------------------
            */

            $invoice = Invoice::query()
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Relaciones
            |--------------------------------------------------------------------------
            */

            $invoice->loadMissing([
                'user',
                'cashbackCampaign',
            ]);

            $user = $invoice->user;

            if (!$user) {
                throw new Exception(
                    'La factura no tiene usuario.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Buscar movimientos originales de la factura
            |--------------------------------------------------------------------------
            |
            | Solo consideramos ingresos.
            |
            | Las reversiones anteriores son egresos y por tanto
            | no deben volver a revertirse.
            |
            */

            $originalTransactions =
                CashbackTransaction::query()
                ->where(
                    'invoice_id',
                    $invoice->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'movimiento',
                    'ingreso'
                )
                ->whereIn(
                    'tipo',
                    [
                        'factura',
                        'bonificacion',
                    ]
                )
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            /*
            |--------------------------------------------------------------------------
            | No hay movimientos que revertir
            |--------------------------------------------------------------------------
            */

            if ($originalTransactions->isEmpty()) {

                /*
                | Si la factura nunca generó cashback,
                | no hay nada financiero que revertir.
                |
                */

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Evitar doble reversión
            |--------------------------------------------------------------------------
            |
            | Calculamos cuánto de cada tipo ya fue revertido.
            |
            */

            $reversedTransactions =
                CashbackTransaction::query()
                ->where(
                    'invoice_id',
                    $invoice->id
                )
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'movimiento',
                    'egreso'
                )
                ->whereIn(
                    'tipo',
                    [
                        'factura',
                        'bonificacion',
                    ]
                )
                ->selectRaw(
                    'tipo, COALESCE(SUM(valor), 0) as total'
                )
                ->groupBy('tipo')
                ->lockForUpdate()
                ->get()
                ->keyBy('tipo');

            /*
            |--------------------------------------------------------------------------
            | Calcular importes pendientes de reversión
            |--------------------------------------------------------------------------
            */

            $cashbackOriginal = 0.0;
            $bonusOriginal = 0.0;

            foreach ($originalTransactions as $transaction) {

                if ($transaction->tipo === 'factura') {

                    $cashbackOriginal +=
                        (float) $transaction->valor;
                }

                if ($transaction->tipo === 'bonificacion') {

                    $bonusOriginal +=
                        (float) $transaction->valor;
                }
            }

            $cashbackRevertido =
                isset($reversedTransactions['factura'])
                ? (float) $reversedTransactions['factura']->total
                : 0.0;

            $bonusRevertido =
                isset($reversedTransactions['bonificacion'])
                ? (float) $reversedTransactions['bonificacion']->total
                : 0.0;

            $cashbackPendiente = round(
                $cashbackOriginal - $cashbackRevertido,
                2
            );

            $bonusPendiente = round(
                $bonusOriginal - $bonusRevertido,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Nada pendiente
            |--------------------------------------------------------------------------
            */

            if (
                $cashbackPendiente <= 0 &&
                $bonusPendiente <= 0
            ) {
                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Bloquear usuario
            |--------------------------------------------------------------------------
            |
            | Es fundamental para evitar que dos operaciones financieras
            | modifiquen el saldo simultáneamente.
            |
            */

            $user = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            /*
            |--------------------------------------------------------------------------
            | Revertir cashback principal
            |--------------------------------------------------------------------------
            */

            if ($cashbackPendiente > 0) {

                $user->cashback_total =
                    round(
                        (float) $user->cashback_total
                            - $cashbackPendiente,
                        2
                    );

                $user->cashback_available =
                    round(
                        (float) $user->cashback_available
                            - $cashbackPendiente,
                        2
                    );

                $user->save();

                /*
                |----------------------------------------------------------------------
                | Registrar egreso de reversión
                |----------------------------------------------------------------------
                */

                CashbackTransaction::create([
                    'user_id' =>
                    $user->id,

                    'invoice_id' =>
                    $invoice->id,

                    'cashback_campaign_id' =>
                    $invoice->cashback_campaign_id,

                    'tipo' =>
                    'factura',

                    'movimiento' =>
                    'egreso',

                    'valor' =>
                    $cashbackPendiente,

                    'saldo_despues' =>
                    $user->cashback_available,

                    'descripcion' =>
                    'Reversión de cashback de la factura '
                        . $invoice->numero_factura_original
                        . ($motivo
                            ? ' - Motivo: ' . $motivo
                            : ''),
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Revertir bono
            |--------------------------------------------------------------------------
            */

            if ($bonusPendiente > 0) {

                $user->cashback_total =
                    round(
                        (float) $user->cashback_total
                            - $bonusPendiente,
                        2
                    );

                $user->cashback_available =
                    round(
                        (float) $user->cashback_available
                            - $bonusPendiente,
                        2
                    );

                $user->save();

                /*
                |----------------------------------------------------------------------
                | Registrar egreso de reversión
                |----------------------------------------------------------------------
                */

                CashbackTransaction::create([
                    'user_id' =>
                    $user->id,

                    'invoice_id' =>
                    $invoice->id,

                    'cashback_campaign_id' =>
                    $invoice->cashback_campaign_id,

                    'tipo' =>
                    'bonificacion',

                    'movimiento' =>
                    'egreso',

                    'valor' =>
                    $bonusPendiente,

                    'saldo_despues' =>
                    $user->cashback_available,

                    'descripcion' =>
                    'Reversión del bono de primera factura '
                        . $invoice->numero_factura_original
                        . ($motivo
                            ? ' - Motivo: ' . $motivo
                            : ''),
                ]);
            }
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
     *
     * Se calcula sobre el saldo neto de bonificaciones.
     *
     * Una bonificación que fue completamente revertida
     * no impide que posteriormente pueda existir una
     * nueva primera factura válida.
     */
    private function calculateFirstInvoiceBonus(
        User $user
    ): float {

        $bonificacionesIngresadas =
            (float) CashbackTransaction::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'tipo',
                    'bonificacion'
                )
                ->where(
                    'movimiento',
                    'ingreso'
                )
                ->sum('valor');

        $bonificacionesRevertidas =
            (float) CashbackTransaction::query()
                ->where(
                    'user_id',
                    $user->id
                )
                ->where(
                    'tipo',
                    'bonificacion'
                )
                ->where(
                    'movimiento',
                    'egreso'
                )
                ->sum('valor');

        $bonificacionNeta = round(
            $bonificacionesIngresadas
                - $bonificacionesRevertidas,
            2
        );

        return $bonificacionNeta > 0
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
