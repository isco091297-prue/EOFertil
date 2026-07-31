<?php

namespace App\Services;

use App\Models\CashbackCampaign;
use App\Models\CashbackTransaction;
use App\Models\Invoice;
use App\Models\User;
use Exception;

class CashbackModuleService
{
    /**
     * Obtener la campaña de cashback vigente.
     */
    public function currentCampaign(): ?CashbackCampaign
    {
        return CashbackCampaign::vigentes()
            ->latest('fecha_inicio')
            ->first();
    }

    /**
     * Obtener el saldo del usuario.
     *
     * @throws Exception
     */
    public function balance(User $user): array
    {
        if (!$user->is_active) {
            throw new Exception(
                'El usuario se encuentra inactivo.'
            );
        }

        return [
            'cashback_total' => (float) $user->cashback_total,
            'cashback_claimed' => (float) $user->cashback_claimed,
            'cashback_available' => (float) $user->cashback_available,
        ];
    }

    /**
     * Obtener historial de cashback del usuario.
     *
     * @throws Exception
     */
    public function history(User $user, int $perPage = 15)
    {
        if (!$user->is_active) {
            throw new Exception(
                'El usuario se encuentra inactivo.'
            );
        }

        return CashbackTransaction::query()
            ->where('user_id', $user->id)
            ->with([
                'cashbackCampaign:id,nombre',
                'invoice:id,numero_factura_original,estado',
            ])
            ->latest()
            ->paginate($perPage);
    }

    /**
     * Obtener el detalle de una factura.
     *
     * @throws Exception
     */
    public function showInvoice(User $user, int $invoiceId): Invoice
    {
        if (!$user->is_active) {
            throw new Exception(
                'El usuario se encuentra inactivo.'
            );
        }

        $invoice = Invoice::with([
            'cashbackCampaign',
            'branch',
            'items.product',
        ])
            ->where('id', $invoiceId)
            ->where('user_id', $user->id)
            ->first();

        if (!$invoice) {
            throw new Exception(
                'La factura no existe.'
            );
        }

        return $invoice;
    }
}
