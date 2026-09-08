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
     * Este método se utiliza cuando la factura pasa a CONFIRMADA.
     *
     * En este punto:
     *
     * - Se calcula el cashback.
     * - Se actualiza la factura.
     * - Se acredita el cashback al usuario.
     * - Se registra el movimiento.
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
            | Calcular cashback
            |--------------------------------------------------------------------------
            |
            | IMPORTANTE:
            |
            | El porcentaje utilizado es el de la campaña que está
            | guardada en la propia factura.
            |
            | No utilizamos la campaña actual del sistema.
            |
            */

            $cashback = $this->calculateCashback(
                $invoice,
                $campaign
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
            | El ranking/acumulado NO se actualiza aquí.
            |
            | InvoiceAdminService se encarga posteriormente
            | de reconstruirlo.
            |
            */

            return $transaction;
        });
    }

    /**
     * Calcular cashback de una factura sin acreditar dinero.
     *
     * Este método se utiliza cuando el administrador modifica
     * una factura que todavía está en estado "procesando".
     *
     * Ejemplo:
     *
     * $40 × 1% = $0.40
     *
     * Si el administrador corrige la factura a $30:
     *
     * $30 × 1% = $0.30
     *
     * La factura permanece "procesando".
     *
     * NO se modifica el saldo del usuario.
     * NO se crean movimientos de cashback.
     * NO se confirma la factura.
     *
     * @throws Exception
     */
    public function recalculatePending(
        Invoice $invoice
    ): Invoice {

        $invoice->loadMissing([
            'user',
            'cashbackCampaign',
            'branch',
        ]);

        $user = $invoice->user;

        $campaign = $invoice->cashbackCampaign;

        /*
        |--------------------------------------------------------------------------
        | Validar campaña y usuario
        |--------------------------------------------------------------------------
        */

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

        if (
            (float) $invoice->total_productos_participantes
            <= 0
        ) {
            throw new Exception(
                'No existen productos participantes.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Calcular cashback
        |--------------------------------------------------------------------------
        |
        | Se utiliza EXACTAMENTE la campaña vinculada a la factura.
        |
        */

        $cashback = $this->calculateCashback(
            $invoice,
            $campaign
        );

        /*
        |--------------------------------------------------------------------------
        | Guardar cálculo
        |--------------------------------------------------------------------------
        |
        | Solamente guardamos el resultado calculado.
        |
        | NO cambiamos el estado.
        | NO actualizamos saldo.
        | NO creamos transacciones.
        |
        */

        $invoice->update([
            'porcentaje_cashback' =>
            $campaign->porcentaje,

            'cashback_generado' =>
            $cashback,

            'estado' =>
            'procesando',
        ]);

        return $invoice->fresh();
    }

    /**
     * Calcular el cashback de una factura.
     */
    public function calculateCashback(
        Invoice $invoice,
        CashbackCampaign $campaign
    ): float {

        return round(
            (
                (float) $invoice->total_productos_participantes
                *
                (float) $campaign->porcentaje
            ) / 100,
            2
        );
    }

    /**
     * Revertir los efectos de cashback de una factura.
     *
     * Este método mantiene una transacción propia para poder utilizarse
     * de forma independiente.
     *
     * Para operaciones administrativas que ya están dentro de una
     * transacción mayor, InvoiceAdminService utiliza
     * reverseInvoiceInternal().
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

            $this->reverseInvoiceInternal(
                $invoice,
                $motivo
            );
        });
    }

    /**
     * Reversión interna de cashback.
     *
     * Este método NO abre una nueva transacción.
     *
     * Debe utilizarse cuando la operación ya está protegida
     * por una transacción superior.
     *
     * @throws Exception
     */
    public function reverseInvoiceInternal(
        Invoice $invoice,
        ?string $motivo = null
    ): void {

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
        | Solamente consideramos ingresos originales.
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
        |
        | Esto ocurre, por ejemplo, al anular una factura que todavía
        | estaba "procesando".
        |
        | En ese caso simplemente no hay dinero que devolver.
        |
        */

        if ($originalTransactions->isEmpty()) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Buscar reversiones anteriores
        |--------------------------------------------------------------------------
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
        | Calcular importes originales
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

        /*
        |--------------------------------------------------------------------------
        | Calcular importes ya revertidos
        |--------------------------------------------------------------------------
        */

        $cashbackRevertido =
            isset($reversedTransactions['factura'])
            ? (float) $reversedTransactions['factura']->total
            : 0.0;

        $bonusRevertido =
            isset($reversedTransactions['bonificacion'])
            ? (float) $reversedTransactions['bonificacion']->total
            : 0.0;

        /*
        |--------------------------------------------------------------------------
        | Pendiente de reversión
        |--------------------------------------------------------------------------
        */

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
        | Evitar doble reversión
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
    }

    /**
     * Validaciones para generar cashback.
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


        if (
            $invoice->estado === 'confirmada'
            && (float) $invoice->cashback_generado > 0
        ) {
            throw new Exception(
                'La factura ya generó cashback.'
            );
        }

        if (
            (float) $invoice->total_productos_participantes
            <= 0
        ) {
            throw new Exception(
                'No existen productos participantes.'
            );
        }
    }

    /**
     * Bono primera factura.
     *
     * Se calcula sobre el saldo neto de bonificaciones.
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
