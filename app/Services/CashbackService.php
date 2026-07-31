<?php

namespace App\Services;

use App\Models\CashbackTransaction;
use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use Exception;

class CashbackService
{
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
            | Calcular cashback
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
            | Actualizar factura
            |--------------------------------------------------------------------------
            */

            $invoice->update([
                'porcentaje_cashback' => $campaign->porcentaje,
                'cashback_generado' => $cashback,
                'estado' => 'confirmada',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Actualizar usuario
            |--------------------------------------------------------------------------
            */

            $nuevoTotal = ($user->cashback_total ?? 0) + $cashback;

            $nuevoDisponible = ($user->cashback_available ?? 0) + $cashback;

            $user->update([
                'cashback_total' => $nuevoTotal,
                'cashback_available' => $nuevoDisponible,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar movimiento
            |--------------------------------------------------------------------------
            */

            return CashbackTransaction::create([
                'user_id' => $user->id,
                'invoice_id' => $invoice->id,
                'cashback_campaign_id' => $campaign->id,
                'tipo' => 'factura',
                'movimiento' => 'ingreso',
                'valor' => $cashback,
                'saldo_despues' => $nuevoDisponible,
                'descripcion' => 'Cashback generado por la factura ' . $invoice->numero_factura_original,
            ]);
        });
    }
}
