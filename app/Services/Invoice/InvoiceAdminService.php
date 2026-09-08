<?php

namespace App\Services\Invoice;

use App\Models\CashbackCampaign;
use App\Models\CampaignUserRanking;
use App\Models\Invoice;
use App\Models\InvoiceAudit;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceAdminService
{
    /**
     * Aprobar una factura pendiente.
     *
     * Al aprobar:
     *
     * 1. Se verifica que la factura esté en procesando.
     * 2. Se genera el cashback.
     * 3. Se procesa el ranking/acumulado.
     * 4. Se registra la auditoría.
     */
    public function approve(
        Invoice $invoice,
        int $adminUserId,
        ?string $motivo = null
    ): Invoice {
        return DB::transaction(function () use (
            $invoice,
            $adminUserId,
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
            | Validar estado
            |--------------------------------------------------------------------------
            */

            if ($invoice->estado !== 'procesando') {
                throw new RuntimeException(
                    'Solo se pueden aprobar facturas pendientes de revisión.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Guardar estado anterior
            |--------------------------------------------------------------------------
            */

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Generar cashback
            |--------------------------------------------------------------------------
            */

            app(\App\Services\Cashback\CashbackService::class)
                ->generate($invoice);

            /*
            |--------------------------------------------------------------------------
            | Procesar ranking / acumulado
            |--------------------------------------------------------------------------
            */

            app(\App\Services\Ranking\RankingCalculatorService::class)
                ->process($invoice);

            /*
            |--------------------------------------------------------------------------
            | Recargar factura
            |--------------------------------------------------------------------------
            */

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Registrar auditoría
            |--------------------------------------------------------------------------
            */

            $this->createAudit(
                invoice: $invoice,
                adminUserId: $adminUserId,
                accion: 'aprobar',
                motivo: $motivo,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: $invoice->estado,
                datosAnteriores: $datosAnteriores,
                datosNuevos: $this->invoiceSnapshot($invoice),
            );

            return $invoice;
        });
    }

    /**
     * Anular una factura confirmada.
     *
     * Toda la operación se ejecuta dentro de una única transacción:
     *
     * - reversión financiera;
     * - cambio de estado;
     * - reconstrucción del ranking;
     * - auditoría.
     */
    public function annul(
        Invoice $invoice,
        int $adminUserId,
        ?string $motivo = null
    ): Invoice {

        return DB::transaction(function () use (
            $invoice,
            $adminUserId,
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
            | Validar estado
            |--------------------------------------------------------------------------
            */

            if ($invoice->estado !== 'confirmada') {
                throw new RuntimeException(
                    'Solo se pueden anular facturas confirmadas.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Estado y snapshot anterior
            |--------------------------------------------------------------------------
            */

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Revertir cashback
            |--------------------------------------------------------------------------
            */

            app(\App\Services\Cashback\CashbackService::class)
                ->reverseInvoice(
                    $invoice,
                    $motivo
                );

            /*
            |--------------------------------------------------------------------------
            | Marcar factura como anulada
            |--------------------------------------------------------------------------
            */

            $invoice->update([
                'estado' => 'anulada',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Reconstruir ranking / acumulado
            |--------------------------------------------------------------------------
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            /*
            |--------------------------------------------------------------------------
            | Recargar factura
            |--------------------------------------------------------------------------
            */

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Registrar auditoría
            |--------------------------------------------------------------------------
            */

            $this->createAudit(
                invoice: $invoice,
                adminUserId: $adminUserId,
                accion: 'anular',
                motivo: $motivo,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: $invoice->estado,
                datosAnteriores: $datosAnteriores,
                datosNuevos: $this->invoiceSnapshot($invoice),
            );

            return $invoice;
        });
    }

    /**
     * Reconstruir los rankings de un usuario.
     *
     * Este método público mantiene su comportamiento actual
     * y abre una transacción cuando se utiliza de forma independiente.
     */
    public function rebuildRankingsForUser(
        int $userId
    ): void {

        DB::transaction(function () use ($userId) {

            $this->rebuildRankingsForUserInternal(
                $userId
            );
        });
    }

    /**
     * Reconstrucción interna de rankings.
     *
     * IMPORTANTE:
     *
     * Este método NO abre una nueva transacción.
     *
     * Esto permite utilizarlo dentro de operaciones como:
     *
     * annul()
     *
     * donde ya existe una transacción principal.
     */
    protected function rebuildRankingsForUserInternal(
        int $userId
    ): void {

        /*
        |--------------------------------------------------------------------------
        | Obtener campañas relevantes
        |--------------------------------------------------------------------------
        */

        $campaignIdsFromRankings = CampaignUserRanking::query()
            ->where('user_id', $userId)
            ->pluck('cashback_campaign_id');

        $userInvoiceDates = Invoice::query()
            ->where('user_id', $userId)
            ->pluck('fecha_factura');

        /*
        |--------------------------------------------------------------------------
        | Si no hay facturas ni rankings
        |--------------------------------------------------------------------------
        */

        if (
            $campaignIdsFromRankings->isEmpty()
            && $userInvoiceDates->isEmpty()
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener campañas
        |--------------------------------------------------------------------------
        */

        $campaigns = CashbackCampaign::query()
            ->where(function ($query) use (
                $campaignIdsFromRankings,
                $userInvoiceDates
            ) {

                if ($campaignIdsFromRankings->isNotEmpty()) {

                    $query->whereIn(
                        'id',
                        $campaignIdsFromRankings
                    );
                }

                if ($userInvoiceDates->isNotEmpty()) {

                    $query->orWhere(function ($query) use (
                        $userInvoiceDates
                    ) {

                        foreach ($userInvoiceDates as $date) {

                            $query->orWhere(function ($query) use (
                                $date
                            ) {

                                $query
                                    ->whereDate(
                                        'fecha_inicio',
                                        '<=',
                                        $date
                                    )
                                    ->whereDate(
                                        'fecha_fin',
                                        '>=',
                                        $date
                                    );
                            });
                        }
                    });
                }
            })
            ->get();

        /*
        |--------------------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------------------
        */

        $user = \App\Models\User::findOrFail(
            $userId
        );

        /*
        |--------------------------------------------------------------------------
        | Procesar campañas
        |--------------------------------------------------------------------------
        */

        foreach ($campaigns as $campaign) {

            $isCashbackRanking =
                $campaign->campaign_type === 'cashback'
                && $campaign->ranking_enabled;

            $isAccumulatedRanking =
                $campaign->campaign_type === 'ranking_accumulated';

            if (
                !$isCashbackRanking
                && !$isAccumulatedRanking
            ) {
                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Facturas válidas
            |--------------------------------------------------------------------------
            */

            $invoices = Invoice::query()
                ->with([
                    'user',
                    'branch',
                ])
                ->where('user_id', $userId)
                ->where('estado', 'confirmada')
                ->whereDate(
                    'fecha_factura',
                    '>=',
                    $campaign->fecha_inicio
                )
                ->whereDate(
                    'fecha_factura',
                    '<=',
                    $campaign->fecha_fin
                )
                ->orderBy('id')
                ->get();

            $salesTotal = 0.0;

            $cashbackTotal = 0.0;

            $invoiceCount = 0;

            /*
            |--------------------------------------------------------------------------
            | Procesar facturas
            |--------------------------------------------------------------------------
            */

            foreach ($invoices as $invoice) {

                if (!$this->campaignAppliesToInvoice(
                    $campaign,
                    $invoice,
                    $user
                )) {
                    continue;
                }

                $salesTotal +=
                    (float) $invoice->total_productos_participantes;

                if ($isCashbackRanking) {

                    $cashbackTotal +=
                        (float) $invoice->cashback_generado;
                }

                $invoiceCount++;
            }

            /*
            |--------------------------------------------------------------------------
            | Sin facturas válidas
            |--------------------------------------------------------------------------
            */

            if ($invoiceCount === 0) {

                CampaignUserRanking::query()
                    ->where(
                        'cashback_campaign_id',
                        $campaign->id
                    )
                    ->where(
                        'user_id',
                        $userId
                    )
                    ->delete();

                continue;
            }

            /*
            |--------------------------------------------------------------------------
            | Crear / actualizar ranking
            |--------------------------------------------------------------------------
            */

            $ranking = CampaignUserRanking::query()
                ->firstOrNew([
                    'cashback_campaign_id' =>
                    $campaign->id,

                    'user_id' =>
                    $userId,
                ]);

            /*
            |--------------------------------------------------------------------------
            | Ubicación actual
            |--------------------------------------------------------------------------
            */

            $ranking->warehouse_id =
                $user->warehouse_id;

            $ranking->zone_id =
                $user->zone_id;

            $ranking->branch_id =
                $user->branch_id;

            /*
            |--------------------------------------------------------------------------
            | Valores reconstruidos
            |--------------------------------------------------------------------------
            */

            $ranking->sales_total =
                round($salesTotal, 2);

            $ranking->cashback_total =
                round($cashbackTotal, 2);

            $ranking->invoice_count =
                $invoiceCount;

            $ranking->position = 0;

            $ranking->save();
        }
    }

    /**
     * Determinar si una campaña aplica a una factura.
     *
     * Utiliza exactamente la misma lógica
     * que RankingCalculatorService.
     */
    protected function campaignAppliesToInvoice(
        CashbackCampaign $campaign,
        Invoice $invoice,
        \App\Models\User $user
    ): bool {

        /*
        |--------------------------------------------------------------------------
        | Todos participan
        |--------------------------------------------------------------------------
        */

        if ($campaign->participant_type === 'all') {
            return true;
        }

        /*
        |--------------------------------------------------------------------------
        | Consulta de alcances
        |--------------------------------------------------------------------------
        */

        $scopeQuery = $campaign->scopes();

        /*
        |--------------------------------------------------------------------------
        | Almacén
        |--------------------------------------------------------------------------
        */

        if ($campaign->participant_type === 'warehouse') {

            if (!$user->warehouse_id) {
                return false;
            }

            return $scopeQuery
                ->where(
                    'warehouse_id',
                    $user->warehouse_id
                )
                ->where(
                    'required',
                    true
                )
                ->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | Zona
        |--------------------------------------------------------------------------
        */

        if ($campaign->participant_type === 'zone') {

            if (!$user->zone_id) {
                return false;
            }

            return $scopeQuery
                ->where(
                    'zone_id',
                    $user->zone_id
                )
                ->where(
                    'required',
                    true
                )
                ->exists();
        }

        /*
        |--------------------------------------------------------------------------
        | Sucursal
        |--------------------------------------------------------------------------
        */

        if ($campaign->participant_type === 'branch') {

            return $scopeQuery
                ->where(
                    'branch_id',
                    $invoice->branch_id
                )
                ->where(
                    'required',
                    true
                )
                ->exists();
        }

        return false;
    }

    /**
     * Crear registro de auditoría.
     */
    protected function createAudit(
        Invoice $invoice,
        int $adminUserId,
        string $accion,
        ?string $motivo,
        ?string $estadoAnterior,
        ?string $estadoNuevo,
        ?array $datosAnteriores,
        ?array $datosNuevos
    ): InvoiceAudit {

        return InvoiceAudit::create([
            'invoice_id' =>
            $invoice->id,

            'admin_user_id' =>
            $adminUserId,

            'accion' =>
            $accion,

            'motivo' =>
            $motivo,

            'estado_anterior' =>
            $estadoAnterior,

            'estado_nuevo' =>
            $estadoNuevo,

            'datos_anteriores' =>
            $datosAnteriores,

            'datos_nuevos' =>
            $datosNuevos,
        ]);
    }

    /**
     * Crear fotografía de los valores de una factura.
     */
    protected function invoiceSnapshot(
        Invoice $invoice
    ): array {

        $invoice->loadMissing([
            'items.product',
        ]);

        return [

            'id' =>
            $invoice->id,

            'numero_factura_original' =>
            $invoice->numero_factura_original,

            'numero_factura_normalizado' =>
            $invoice->numero_factura_normalizado,

            'fecha_factura' =>
            optional(
                $invoice->fecha_factura
            )->format('Y-m-d'),

            'total_factura' =>
            (float) $invoice->total_factura,

            'total_productos_participantes' =>
            (float) $invoice->total_productos_participantes,

            'porcentaje_cashback' =>
            (float) $invoice->porcentaje_cashback,

            'cashback_generado' =>
            (float) $invoice->cashback_generado,

            'estado' =>
            $invoice->estado,

            'productos' =>
            $invoice->items
                ->map(function ($item) {

                    return [
                        'invoice_item_id' =>
                        $item->id,

                        'product_id' =>
                        $item->product_id,

                        'product_name' =>
                        $item->product?->name,

                        'valor' =>
                        (float) $item->valor,
                    ];
                })
                ->values()
                ->toArray(),
        ];
    }
}
