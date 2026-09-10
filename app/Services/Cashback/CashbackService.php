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
    private const FIRST_INVOICE_BONUS = 5.00;

    /**
     * Acredita el cashback al registrar la factura.
     *
     * La factura permanece en estado "procesando".
     */
    public function creditPending(
        Invoice $invoice
    ): CashbackTransaction {

        return DB::transaction(function () use ($invoice) {

            $invoice = Invoice::query()
                ->with([
                    'user',
                    'cashbackCampaign',
                    'branch',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $invoice->user;
            $campaign = $invoice->cashbackCampaign;

            $this->validateInvoice(
                $invoice,
                $campaign,
                $user
            );

            if ($invoice->estado !== 'procesando') {
                throw new Exception(
                    'Solo se puede acreditar una factura en revisión.'
                );
            }

            $alreadyCredited = CashbackTransaction::query()
                ->where('invoice_id', $invoice->id)
                ->where('tipo', 'factura')
                ->where('movimiento', 'ingreso')
                ->exists();

            if ($alreadyCredited) {
                throw new Exception(
                    'La factura ya tiene cashback acreditado.'
                );
            }

            $cashback = $this->calculateCashback(
                $invoice,
                $campaign
            );

            $firstInvoiceBonus =
                $this->calculateFirstInvoiceBonus($user);

            $invoice->update([
                'porcentaje_cashback' =>
                $campaign->porcentaje,

                'cashback_generado' =>
                $cashback,

                'estado' =>
                'procesando',
            ]);

            $this->updateUserBalances(
                $user,
                $cashback,
                $firstInvoiceBonus
            );

            return $this->createCashbackTransaction(
                $user,
                $invoice,
                $campaign,
                $cashback,
                $firstInvoiceBonus
            );
        });
    }

    /**
     * Mantiene este método por compatibilidad.
     *
     * Actualmente se utiliza solamente para operaciones que necesiten
     * generar cashback y confirmar directamente.
     */
    public function generate(
        Invoice $invoice
    ): CashbackTransaction {

        return DB::transaction(function () use ($invoice) {

            $invoice = Invoice::query()
                ->with([
                    'user',
                    'cashbackCampaign',
                    'branch',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $invoice->user;
            $campaign = $invoice->cashbackCampaign;

            $this->validateInvoice(
                $invoice,
                $campaign,
                $user
            );

            $cashback = $this->calculateCashback(
                $invoice,
                $campaign
            );

            $firstInvoiceBonus =
                $this->calculateFirstInvoiceBonus($user);

            $invoice->update([
                'porcentaje_cashback' =>
                $campaign->porcentaje,

                'cashback_generado' =>
                $cashback,

                'estado' =>
                'confirmada',
            ]);

            $this->updateUserBalances(
                $user,
                $cashback,
                $firstInvoiceBonus
            );

            return $this->createCashbackTransaction(
                $user,
                $invoice,
                $campaign,
                $cashback,
                $firstInvoiceBonus
            );
        });
    }

    /**
     * Recalcula únicamente los valores de la factura.
     *
     * No modifica saldos ni movimientos.
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
            (float) $invoice->total_productos_participantes <= 0
        ) {
            throw new Exception(
                'No existen productos participantes.'
            );
        }

        $cashback = $this->calculateCashback(
            $invoice,
            $campaign
        );

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
     * Corrige financieramente el cashback de una factura pendiente.
     *
     * La factura ya recibió el cashback original al registrarse.
     *
     * Ejemplo:
     *
     * Original: $100 → $1.00
     * Corregida: $70 → $0.70
     *
     * Se revierte $1.00 y se acredita $0.70.
     */
    public function adjustPendingCashback(
        Invoice $invoice
    ): Invoice {

        return DB::transaction(function () use ($invoice) {

            $invoice = Invoice::query()
                ->with([
                    'user',
                    'cashbackCampaign',
                    'branch',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user = $invoice->user;
            $campaign = $invoice->cashbackCampaign;

            $this->validateInvoice(
                $invoice,
                $campaign,
                $user
            );

            if ($invoice->estado !== 'procesando') {
                throw new Exception(
                    'Solo se puede corregir una factura pendiente.'
                );
            }

            $this->reverseInvoiceCashbackOnlyInternal(
                $invoice
            );

            $cashback = $this->calculateCashback(
                $invoice,
                $campaign
            );

            $invoice->update([
                'porcentaje_cashback' =>
                $campaign->porcentaje,

                'cashback_generado' =>
                $cashback,

                'estado' =>
                'procesando',
            ]);

            $user = User::query()
                ->whereKey($user->id)
                ->lockForUpdate()
                ->firstOrFail();

            $user->cashback_total = round(
                (float) $user->cashback_total
                    + $cashback,
                2
            );

            $user->cashback_available = round(
                (float) $user->cashback_available
                    + $cashback,
                2
            );

            $user->save();

            CashbackTransaction::create([
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
                $user->cashback_available,

                'descripcion' =>
                'Cashback corregido de la factura '
                    . $invoice->numero_factura_original,
            ]);

            return $invoice->fresh();
        });
    }

    /**
     * Calcula cashback.
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
     * Reversión pública.
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
     * Revierte cashback y bono de una factura.
     */
    public function reverseInvoiceInternal(
        Invoice $invoice,
        ?string $motivo = null
    ): void {

        $invoice = Invoice::query()
            ->whereKey($invoice->id)
            ->lockForUpdate()
            ->firstOrFail();

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

        $originalTransactions =
            CashbackTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->where('movimiento', 'ingreso')
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

        if ($originalTransactions->isEmpty()) {
            return;
        }

        $reversedTransactions =
            CashbackTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->where('movimiento', 'egreso')
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
            $cashbackOriginal -
                $cashbackRevertido,
            2
        );

        $bonusPendiente = round(
            $bonusOriginal -
                $bonusRevertido,
            2
        );

        if (
            $cashbackPendiente <= 0 &&
            $bonusPendiente <= 0
        ) {
            return;
        }

        $user = User::query()
            ->whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        if ($cashbackPendiente > 0) {

            $user->cashback_total = round(
                (float) $user->cashback_total
                    - $cashbackPendiente,
                2
            );

            $user->cashback_available = round(
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
                    . (
                        $motivo
                        ? ' - Motivo: ' . $motivo
                        : ''
                    ),
            ]);
        }

        if ($bonusPendiente > 0) {

            $user->cashback_total = round(
                (float) $user->cashback_total
                    - $bonusPendiente,
                2
            );

            $user->cashback_available = round(
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
                    . (
                        $motivo
                        ? ' - Motivo: ' . $motivo
                        : ''
                    ),
            ]);
        }
    }

    /**
     * Revierte solamente el cashback principal.
     *
     * Se utiliza para modificar una factura.
     * El bono de primera factura NO se toca.
     */
    public function reverseInvoiceCashbackOnlyInternal(
        Invoice $invoice
    ): void {

        $invoice->loadMissing([
            'user',
        ]);

        $user = $invoice->user;

        if (!$user) {
            throw new Exception(
                'La factura no tiene usuario.'
            );
        }

        $original = CashbackTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->where('tipo', 'factura')
            ->where('movimiento', 'ingreso')
            ->sum('valor');

        $reversed = CashbackTransaction::query()
            ->where('invoice_id', $invoice->id)
            ->where('user_id', $user->id)
            ->where('tipo', 'factura')
            ->where('movimiento', 'egreso')
            ->sum('valor');

        $pendiente = round(
            (float) $original -
                (float) $reversed,
            2
        );

        if ($pendiente <= 0) {
            return;
        }

        $user = User::query()
            ->whereKey($user->id)
            ->lockForUpdate()
            ->firstOrFail();

        $user->cashback_total = round(
            (float) $user->cashback_total
                - $pendiente,
            2
        );

        $user->cashback_available = round(
            (float) $user->cashback_available
                - $pendiente,
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
            $pendiente,

            'saldo_despues' =>
            $user->cashback_available,

            'descripcion' =>
            'Reversión para corrección de la factura '
                . $invoice->numero_factura_original,
        ]);
    }

    /**
     * Validar factura.
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
            &&
            (float) $invoice->cashback_generado > 0
        ) {
            throw new Exception(
                'La factura ya fue procesada.'
            );
        }

        if (
            (float) $invoice->total_productos_participantes <= 0
        ) {
            throw new Exception(
                'No existen productos participantes.'
            );
        }
    }

    /**
     * Bono de primera factura.
     */
    private function calculateFirstInvoiceBonus(
        User $user
    ): float {

        $bonificacionesIngresadas =
            (float) CashbackTransaction::query()
                ->where('user_id', $user->id)
                ->where('tipo', 'bonificacion')
                ->where('movimiento', 'ingreso')
                ->sum('valor');

        $bonificacionesRevertidas =
            (float) CashbackTransaction::query()
                ->where('user_id', $user->id)
                ->where('tipo', 'bonificacion')
                ->where('movimiento', 'egreso')
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
     * Crear movimientos.
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
            (float) $user->cashback_available
                - $bonus,

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
}
