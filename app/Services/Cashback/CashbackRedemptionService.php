<?php

namespace App\Services\Cashback;

use App\Models\CashbackRedemption;
use App\Models\CashbackTransaction;
use App\Models\User;
use App\Services\Telegram\TelegramService;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class CashbackRedemptionService
{
    public function __construct(
        private readonly TelegramService $telegramService
    ) {}

    /**
     * Registrar una solicitud de canje.
     *
     * El proceso completo es:
     *
     * 1. Validar monto.
     * 2. Bloquear al usuario.
     * 3. Verificar saldo disponible.
     * 4. Descontar cashback disponible.
     * 5. Incrementar cashback reclamado.
     * 6. Crear solicitud de canje.
     * 7. Registrar movimiento de cashback.
     * 8. Enviar solicitud a Telegram.
     */
    public function redeem(
        User $user,
        float $monto
    ): CashbackRedemption {

        if (! $user->is_active) {
            throw new RuntimeException(
                'El usuario se encuentra inactivo.'
            );
        }

        $monto = round($monto, 2);

        if ($monto <= 0) {
            throw new RuntimeException(
                'El monto del canje debe ser mayor a cero.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | TRANSACCIÓN PRINCIPAL
        |--------------------------------------------------------------------------
        |
        | El saldo y el registro del canje se modifican juntos.
        |
        */

        $redemption = DB::transaction(function () use (
            $user,
            $monto
        ) {

            /*
            |--------------------------------------------------------------------------
            | Bloquear usuario
            |--------------------------------------------------------------------------
            |
            | Evita que dos solicitudes simultáneas puedan gastar
            | el mismo saldo disponible.
            |
            */

            $lockedUser = User::query()
                ->lockForUpdate()
                ->find($user->id);

            if (! $lockedUser) {
                throw new RuntimeException(
                    'No fue posible obtener el usuario.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validar saldo
            |--------------------------------------------------------------------------
            */

            $cashbackAvailable = round(
                (float) $lockedUser->cashback_available,
                2
            );

            if ($monto > $cashbackAvailable) {
                throw new RuntimeException(
                    'El monto solicitado supera el cashback disponible de $' .
                        number_format(
                            $cashbackAvailable,
                            2
                        ) .
                        '.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Calcular nuevos saldos
            |--------------------------------------------------------------------------
            */

            $newAvailable = round(
                $cashbackAvailable - $monto,
                2
            );

            $newClaimed = round(
                (float) $lockedUser->cashback_claimed + $monto,
                2
            );

            /*
            |--------------------------------------------------------------------------
            | Actualizar usuario
            |--------------------------------------------------------------------------
            */

            $lockedUser->update([
                'cashback_available' => $newAvailable,
                'cashback_claimed' => $newClaimed,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Crear solicitud de canje
            |--------------------------------------------------------------------------
            |
            | Guardamos los datos bancarios como estaban en el momento
            | exacto de la solicitud.
            |
            */

            $redemption = CashbackRedemption::create([
                'user_id' => $lockedUser->id,

                'warehouse_id' =>
                $lockedUser->warehouse_id,

                'branch_id' =>
                $lockedUser->branch_id,

                'identification' =>
                $lockedUser->identification,

                'bank' =>
                $lockedUser->bank,

                'account_type' =>
                $lockedUser->account_type,

                'account_number' =>
                $lockedUser->account_number,

                'monto' =>
                $monto,

                'estado' =>
                'pendiente',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Registrar movimiento de cashback
            |--------------------------------------------------------------------------
            */

            CashbackTransaction::create([
                'user_id' =>
                $lockedUser->id,

                'invoice_id' =>
                null,

                'cashback_campaign_id' =>
                null,

                'tipo' =>
                'canje',

                'movimiento' =>
                'egreso',

                'valor' =>
                $monto,

                'saldo_despues' =>
                $newAvailable,

                'descripcion' =>
                'Solicitud de canje de cashback por $' .
                    number_format(
                        $monto,
                        2,
                        '.',
                        ''
                    ),
            ]);

            return $redemption->fresh([
                'user',
                'warehouse',
                'branch',
            ]);
        });

        /*
        |--------------------------------------------------------------------------
        | TELEGRAM
        |--------------------------------------------------------------------------
        |
        | El canje YA está registrado.
        |
        | Si Telegram falla, no revertimos el dinero.
        | Guardamos el error para poder revisarlo después.
        |
        */

        try {

            $message = $this->buildTelegramMessage(
                $redemption
            );

            $this->telegramService->sendMessage(
                $message
            );

            $redemption->update([
                'telegram_enviado_at' => now(),
                'telegram_error' => null,
            ]);
        } catch (Throwable $e) {

            $redemption->update([
                'telegram_error' =>
                $e->getMessage(),
            ]);
        }

        return $redemption->fresh([
            'user',
            'warehouse',
            'branch',
        ]);
    }

    /**
     * Construir mensaje que recibirá administración en Telegram.
     */
    private function buildTelegramMessage(
        CashbackRedemption $redemption
    ): string {

        $user = $redemption->user;

        $warehouse = $redemption->warehouse;

        $branch = $redemption->branch;

        $availableAfter = (float) $user->cashback_available;

        $fullName = trim(
            $user->first_name . ' ' . $user->last_name
        );

        return implode("\n", [

            '🔔 <b>SOLICITUD DE CANJE DE CASHBACK</b>',

            '',

            '👤 <b>Usuario:</b> ' .
                $this->escape(
                    $fullName
                ),

            '🪪 <b>Cédula:</b> ' .
                $this->escape(
                    $redemption->identification ?? '-'
                ),

            '🏢 <b>Almacén:</b> ' .
                $this->escape(
                    $warehouse?->name ?? '-'
                ),

            '📍 <b>Sucursal:</b> ' .
                $this->escape(
                    $branch?->name ?? '-'
                ),

            '🏦 <b>Banco:</b> ' .
                $this->escape(
                    $redemption->bank ?? '-'
                ),

            '💳 <b>Tipo de cuenta:</b> ' .
                $this->escape(
                    $redemption->account_type ?? '-'
                ),

            '🔢 <b>Número de cuenta:</b> ' .
                $this->escape(
                    $redemption->account_number ?? '-'
                ),

            '',

            '💰 <b>Monto solicitado:</b> $' .
                number_format(
                    (float) $redemption->monto,
                    2
                ),

            '💵 <b>Cashback disponible después:</b> $' .
                number_format(
                    $availableAfter,
                    2
                ),

            '📌 <b>Estado:</b> PENDIENTE',

            '📅 <b>Fecha:</b> ' .
                $redemption->created_at
                ->format('d/m/Y H:i'),
        ]);
    }

    /**
     * Escapar caracteres para Telegram HTML.
     */
    private function escape(string $value): string
    {
        return htmlspecialchars(
            $value,
            ENT_QUOTES | ENT_SUBSTITUTE,
            'UTF-8'
        );
    }
}
