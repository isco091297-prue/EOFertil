<?php

namespace App\Services;

use App\Models\CashbackTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Exception;

class CashbackService
{
    /**
     * Bono entregado al usuario por registrar
     * su primera factura.
     */
    private const FIRST_INVOICE_BONUS = 5.00;

    /**
     * Genera el cashback de una factura.
     *
     * @throws Exception
     */
    public function generate(Invoice $invoice): CashbackTransaction
    {
        return DB::transaction(function () use ($invoice) {

            /*
            |--------------------------------------------------------------------------
            | Cargar relaciones
            |--------------------------------------------------------------------------
            */

            $invoice->loadMissing([
                'user',
                'cashbackCampaign',
            ]);

            $user = $invoice->user;
            $campaign = $invoice->cashbackCampaign;

            /*
            |--------------------------------------------------------------------------
            | Validaciones
            |--------------------------------------------------------------------------
            */

            if (!$user) {
                throw new Exception(
                    'La factura no tiene un usuario asociado.'
                );
            }

            if (!$campaign) {
                throw new Exception(
                    'La factura no tiene una campaña de cashback asociada.'
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
                    'La factura no tiene productos participantes para generar cashback.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Calcular cashback de la factura
            |--------------------------------------------------------------------------
            */

            $cashback = round(
                ($invoice->total_productos_participantes * $campaign->porcentaje) / 100,
                2
            );

            if ($cashback <= 0) {
                throw new Exception(
                    'El cashback calculado es inválido.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar bono de primera factura
            |--------------------------------------------------------------------------
            |
            | El bono se identifica mediante una transacción independiente
            | de tipo "bonificacion".
            |
            | De esta manera el usuario solamente puede recibirlo una vez.
            |
            */

            $alreadyReceivedFirstInvoiceBonus = CashbackTransaction::where(
                'user_id',
                $user->id
            )
                ->where(
                    'tipo',
                    'bonificacion'
                )
                ->where(
                    'descripcion',
                    'Bono por registrar tu primera factura'
                )
                ->exists();

            $firstInvoiceBonus = $alreadyReceivedFirstInvoiceBonus
                ? 0
                : self::FIRST_INVOICE_BONUS;

            /*
            |--------------------------------------------------------------------------
            | Actualizar factura
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            | cashback_generado contiene solamente el cashback producido
            | por la factura.
            |
            | El bono de $5 es un movimiento independiente.
            |
            */

            $invoice->update([
                'porcentaje_cashback' => $campaign->porcentaje,
                'cashback_generado' => $cashback,
                'estado' => 'confirmada',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Calcular nuevos saldos
            |--------------------------------------------------------------------------
            */

            $nuevoTotal = ($user->cashback_total ?? 0)
                + $cashback
                + $firstInvoiceBonus;

            $nuevoDisponible = ($user->cashback_available ?? 0)
                + $cashback
                + $firstInvoiceBonus;

            /*
            |--------------------------------------------------------------------------
            | Actualizar usuario
            |--------------------------------------------------------------------------
            */

            $user->update([
                'cashback_total' => $nuevoTotal,
                'cashback_available' => $nuevoDisponible,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar movimiento del cashback de la factura
            |--------------------------------------------------------------------------
            */

            $saldoDespuesFactura = ($user->cashback_available ?? 0)
                - $firstInvoiceBonus;

            $cashbackTransaction = CashbackTransaction::create([
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'cashback_campaign_id' => $campaign->id,
                'tipo' => 'factura',
                'movimiento' => 'ingreso',
                'valor' => $cashback,
                'saldo_despues' => $saldoDespuesFactura,
                'descripcion' => 'Cashback generado por la factura '
                    . $invoice->numero_factura_original,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar bono por primera factura
            |--------------------------------------------------------------------------
            */

            if ($firstInvoiceBonus > 0) {

                CashbackTransaction::create([
                    'user_id' => $user->id,
                    'invoice_id' => $invoice->id,
                    'cashback_campaign_id' => $campaign->id,
                    'tipo' => 'bonificacion',
                    'movimiento' => 'ingreso',
                    'valor' => $firstInvoiceBonus,
                    'saldo_despues' => $nuevoDisponible,
                    'descripcion' => 'Bono por registrar tu primera factura',
                ]);
            }

            /*
            |--------------------------------------------------------------------------
            | Retornar transacción principal
            |--------------------------------------------------------------------------
            */

            return $cashbackTransaction;
        });
    }
}
