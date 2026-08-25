<?php

namespace App\Services\Cashback;

use App\Models\CashbackCampaign;
use App\Models\CashbackTransaction;
use App\Models\Invoice;
use App\Models\User;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Exception;

class CashbackModuleService
{
   /**
 * Campaña Cashback vigente.
 *
 * Esta campaña es independiente del ranking acumulado.
 * El ranking acumulado se consulta mediante su propio endpoint.
 */
public function currentCampaign(): ?CashbackCampaign
{
    return CashbackCampaign::vigentes()
        ->where('campaign_type', 'cashback')
        ->latest('fecha_inicio')
        ->first();
}

    /**
     * Saldos del usuario.
     */
    public function balance(
        User $user
    ): array {

        $this->validateUser($user);

        return [

            'cashback_total' =>
            (float) $user->cashback_total,

            'cashback_claimed' =>
            (float) $user->cashback_claimed,

            'cashback_available' =>
            (float) $user->cashback_available,

        ];
    }

    /**
     * Historial.
     */
    public function history(
        User $user,
        int $perPage = 15
    ): LengthAwarePaginator {

        $this->validateUser($user);

        return CashbackTransaction::query()

            ->where(
                'user_id',
                $user->id
            )

            ->with([

                'cashbackCampaign:id,nombre',

                'invoice:id,numero_factura_original,estado',

            ])

            ->latest()

            ->paginate(
                $perPage
            );
    }

    /**
     * Factura.
     */
    public function invoice(
        User $user,
        int $invoiceId
    ): Invoice {

        $this->validateUser($user);

        $invoice = Invoice::query()

            ->with([

                'cashbackCampaign',

                'branch',

                'items.product',

            ])

            ->where(
                'user_id',
                $user->id
            )

            ->find(
                $invoiceId
            );

        if (! $invoice) {

            throw new Exception(
                'La factura no existe.'
            );
        }

        return $invoice;
    }

    /**
     * Validar usuario.
     */
    private function validateUser(
        User $user
    ): void {

        if (! $user->is_active) {

            throw new Exception(
                'El usuario se encuentra inactivo.'
            );
        }
    }
}
