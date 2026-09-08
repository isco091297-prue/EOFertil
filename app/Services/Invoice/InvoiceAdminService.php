<?php

namespace App\Services\Invoice;

use App\Models\CashbackCampaign;
use App\Models\CampaignUserRanking;
use App\Models\Invoice;
use App\Models\InvoiceAudit;
use App\Services\Cashback\CashbackService;
use App\Services\Ranking\RankingCalculatorService;
use Illuminate\Support\Facades\DB;
use RuntimeException;

class InvoiceAdminService
{
    public function __construct(
        protected CashbackService $cashbackService,
        protected RankingCalculatorService $rankingCalculatorService
    ) {}

    /**
     * Aprobar una factura pendiente.
     *
     * Flujo:
     *
     * procesando
     *     ↓
     * calcula/acredita cashback
     *     ↓
     * confirmada
     *     ↓
     * reconstruye acumulado/ranking
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

            $invoice = Invoice::query()
                ->with([
                    'user',
                    'branch',
                    'cashbackCampaign',
                    'items.product',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->estado !== 'procesando') {
                throw new RuntimeException(
                    'Solo se pueden aprobar facturas pendientes de revisión.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Generar cashback
            |--------------------------------------------------------------------------
            |
            | Aquí recién se acredita el cashback al usuario.
            |
            | CashbackService utiliza la campaña guardada
            | en cashback_campaign_id.
            |
            */

            $this->cashbackService->generate(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Reconstruir acumulado / ranking
            |--------------------------------------------------------------------------
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Auditoría
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
     * Modificar una factura.
     *
     * CASO 1:
     *
     * procesando
     *     ↓
     * modificar
     *     ↓
     * recalcular cashback
     *     ↓
     * sigue procesando
     *
     * El cashback calculado queda guardado en la factura,
     * pero todavía NO se acredita al usuario.
     *
     *
     * CASO 2:
     *
     * confirmada
     *     ↓
     * revertir efectos anteriores
     *     ↓
     * modificar
     *     ↓
     * recalcular cashback
     *     ↓
     * acreditar nuevo cashback
     *     ↓
     * sigue confirmada
     *
     * En ambos casos se reconstruye el acumulado/ranking.
     */
    public function update(
        Invoice $invoice,
        array $data,
        int $adminUserId
    ): Invoice {

        return DB::transaction(function () use (
            $invoice,
            $data,
            $adminUserId
        ) {

            $invoice = Invoice::query()
                ->with([
                    'items.product',
                    'user',
                    'cashbackCampaign',
                    'branch',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->estado === 'anulada') {
                throw new RuntimeException(
                    'Una factura anulada no puede modificarse.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $eraConfirmada =
                $invoice->estado === 'confirmada';

            $datosAnteriores =
                $this->invoiceSnapshot(
                    $invoice
                );

            /*
            |--------------------------------------------------------------------------
            | Si estaba confirmada
            |--------------------------------------------------------------------------
            |
            | Primero quitamos completamente los efectos financieros
            | de la versión anterior.
            |
            */

            if ($eraConfirmada) {

                $this->cashbackService
                    ->reverseInvoiceInternal(
                        $invoice,
                        $data['motivo'] ?? null
                    );

                $invoice->cashback_generado = 0;
                $invoice->porcentaje_cashback = 0;

                $invoice->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Actualizar datos generales
            |--------------------------------------------------------------------------
            */

            $invoice->numero_factura_original =
                $data['numero_factura_original'];

            $invoice->numero_factura_normalizado =
                $this->normalizeInvoiceNumber(
                    $data['numero_factura_original']
                );

            $invoice->fecha_factura =
                $data['fecha_factura'];

            $invoice->total_factura =
                $data['total_factura'];

            /*
            |--------------------------------------------------------------------------
            | Actualizar productos
            |--------------------------------------------------------------------------
            |
            | Los invoice_items representan los productos EOFertil
            | registrados en la factura.
            |
            */

            if (
                isset($data['items'])
                && is_array($data['items'])
            ) {

                foreach (
                    $data['items'] as $itemId => $itemData
                ) {

                    $item = $invoice->items
                        ->firstWhere(
                            'id',
                            (int) $itemId
                        );

                    if (!$item) {
                        continue;
                    }

                    $item->valor =
                        $itemData['valor'];

                    $item->save();
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Recalcular total de productos participantes
            |--------------------------------------------------------------------------
            */

            $invoice->total_productos_participantes =
                $invoice->items()->sum('valor');

            /*
            |--------------------------------------------------------------------------
            | MODIFICACIÓN DE PENDIENTE
            |--------------------------------------------------------------------------
            */

            if (!$eraConfirmada) {

                /*
                |------------------------------------------------------------------
                | Recalcular cashback utilizando la campaña ORIGINAL
                | de la factura.
                |------------------------------------------------------------------
                */

                $invoice->cashback_generado = 0;
                $invoice->porcentaje_cashback = 0;
                $invoice->estado = 'procesando';

                $invoice->save();

                $invoice =
                    $this->cashbackService
                    ->recalculatePending(
                        $invoice->fresh()
                    );

                /*
                |------------------------------------------------------------------
                | IMPORTANTE
                |------------------------------------------------------------------
                |
                | La factura sigue procesando.
                |
                | El cashback calculado queda en la factura,
                | pero no se acredita al saldo.
                |
                */

                $invoice->estado = 'procesando';
                $invoice->save();

                /*
                |------------------------------------------------------------------
                | El acumulado/ranking solamente considera confirmadas.
                |------------------------------------------------------------------
                */

                $this->rebuildRankingsForUserInternal(
                    $invoice->user_id
                );
            }

            /*
            |--------------------------------------------------------------------------
            | MODIFICACIÓN DE CONFIRMADA
            |--------------------------------------------------------------------------
            */ else {

                /*
                |------------------------------------------------------------------
                | La factura ya fue revertida arriba.
                |------------------------------------------------------------------
                */

                $invoice->cashback_generado = 0;
                $invoice->porcentaje_cashback = 0;
                $invoice->estado = 'procesando';

                $invoice->save();

                /*
                |------------------------------------------------------------------
                | Generamos nuevamente.
                |------------------------------------------------------------------
                |
                | Esto:
                |
                | - usa la campaña guardada en la factura;
                | - calcula el nuevo cashback;
                | - acredita el nuevo importe;
                | - crea los nuevos movimientos;
                | - vuelve a dejar la factura confirmada.
                |
                */

                $this->cashbackService->generate(
                    $invoice->fresh()
                );

                /*
                |------------------------------------------------------------------
                | Reconstruir acumulado / ranking.
                |------------------------------------------------------------------
                */

                $this->rebuildRankingsForUserInternal(
                    $invoice->user_id
                );
            }

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Auditoría
            |--------------------------------------------------------------------------
            */

            $this->createAudit(
                invoice: $invoice,
                adminUserId: $adminUserId,
                accion: 'modificar',
                motivo: $data['motivo'] ?? null,
                estadoAnterior: $estadoAnterior,
                estadoNuevo: $invoice->estado,
                datosAnteriores: $datosAnteriores,
                datosNuevos: $this->invoiceSnapshot($invoice),
            );

            return $invoice;
        });
    }

    /**
     * Anular una factura.
     *
     * Se puede anular tanto:
     *
     * - procesando
     * - confirmada
     *
     * Si está procesando:
     *
     * no hay cashback acreditado,
     * por lo que simplemente pasa a anulada.
     *
     * Si está confirmada:
     *
     * se revierten cashback y bonificación,
     * y después pasa a anulada.
     */
    public function annul(
        Invoice $invoice,
        int $adminUserId,
        ?string $motivo = null
    ): Invoice {

        if (!$motivo) {
            throw new RuntimeException(
                'El motivo de anulación es obligatorio.'
            );
        }

        return DB::transaction(function () use (
            $invoice,
            $adminUserId,
            $motivo
        ) {

            $invoice = Invoice::query()
                ->with([
                    'user',
                    'cashbackCampaign',
                    'items.product',
                    'branch',
                ])
                ->whereKey($invoice->id)
                ->lockForUpdate()
                ->firstOrFail();

            if ($invoice->estado === 'anulada') {
                throw new RuntimeException(
                    'La factura ya está anulada.'
                );
            }

            if (
                $invoice->estado !== 'procesando'
                && $invoice->estado !== 'confirmada'
            ) {
                throw new RuntimeException(
                    'La factura no puede ser anulada.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $datosAnteriores =
                $this->invoiceSnapshot(
                    $invoice
                );

            /*
            |--------------------------------------------------------------------------
            | Revertir cashback si existe.
            |--------------------------------------------------------------------------
            |
            | Si está procesando y todavía no tiene movimientos,
            | reverseInvoiceInternal() simplemente no hace nada.
            |
            */

            $this->cashbackService
                ->reverseInvoiceInternal(
                    $invoice,
                    $motivo
                );

            /*
            |--------------------------------------------------------------------------
            | Marcar como anulada
            |--------------------------------------------------------------------------
            */

            $invoice->update([
                'estado' =>
                'anulada',

                'cashback_generado' =>
                0,

                'porcentaje_cashback' =>
                0,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Reconstruir acumulado / ranking
            |--------------------------------------------------------------------------
            |
            | Una factura anulada ya no cuenta.
            |
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            $invoice->refresh();

            /*
            |--------------------------------------------------------------------------
            | Auditoría
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
     * No abre una transacción propia.
     */
    protected function rebuildRankingsForUserInternal(
        int $userId
    ): void {

        $campaignIdsFromRankings =
            CampaignUserRanking::query()
            ->where(
                'user_id',
                $userId
            )
            ->pluck(
                'cashback_campaign_id'
            );

        $userInvoiceDates =
            Invoice::query()
            ->where(
                'user_id',
                $userId
            )
            ->pluck(
                'fecha_factura'
            );

        if (
            $campaignIdsFromRankings->isEmpty()
            && $userInvoiceDates->isEmpty()
        ) {
            return;
        }

        /*
        |--------------------------------------------------------------------------
        | Obtener campañas relevantes.
        |--------------------------------------------------------------------------
        */

        $campaigns = CashbackCampaign::query()
            ->where(function ($query) use (
                $campaignIdsFromRankings,
                $userInvoiceDates
            ) {

                if (
                    $campaignIdsFromRankings->isNotEmpty()
                ) {

                    $query->whereIn(
                        'id',
                        $campaignIdsFromRankings
                    );
                }

                if (
                    $userInvoiceDates->isNotEmpty()
                ) {

                    $query->orWhere(function (
                        $query
                    ) use ($userInvoiceDates) {

                        foreach (
                            $userInvoiceDates as $date
                        ) {

                            $query->orWhere(
                                function ($query) use (
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
                                }
                            );
                        }
                    });
                }
            })
            ->get();

        $user = \App\Models\User::findOrFail(
            $userId
        );

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
            | Facturas válidas para ESTA campaña.
            |--------------------------------------------------------------------------
            |
            | MUY IMPORTANTE:
            |
            | No basta con que la fecha caiga dentro del período.
            |
            | La factura debe tener guardado este campaign_id.
            |
            | Así evitamos que una factura del 1% termine contabilizada
            | accidentalmente dentro de otra campaña del 2%.
            |
            */

            $invoices = Invoice::query()
                ->with([
                    'user',
                    'branch',
                ])
                ->where(
                    'user_id',
                    $userId
                )
                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )
                ->where(
                    'estado',
                    'confirmada'
                )
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

            foreach ($invoices as $invoice) {

                if (
                    !$this->campaignAppliesToInvoice(
                        $campaign,
                        $invoice,
                        $user
                    )
                ) {
                    continue;
                }

                /*
                |------------------------------------------------------------------
                | Acumulado de ventas.
                |------------------------------------------------------------------
                */

                $salesTotal +=
                    (float) $invoice
                        ->total_productos_participantes;

                /*
                |------------------------------------------------------------------
                | Cashback acumulado.
                |------------------------------------------------------------------
                */

                if ($isCashbackRanking) {

                    $cashbackTotal +=
                        (float) $invoice
                            ->cashback_generado;
                }

                $invoiceCount++;
            }

            /*
            |--------------------------------------------------------------------------
            | Si no existen facturas válidas
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

            $ranking->warehouse_id =
                $user->warehouse_id;

            $ranking->zone_id =
                $user->zone_id;

            $ranking->branch_id =
                $user->branch_id;

            $ranking->sales_total =
                round(
                    $salesTotal,
                    2
                );

            $ranking->cashback_total =
                round(
                    $cashbackTotal,
                    2
                );

            $ranking->invoice_count =
                $invoiceCount;

            $ranking->position = 0;

            $ranking->save();
        }
    }

    /**
     * Determinar si una campaña aplica a una factura.
     */
    protected function campaignAppliesToInvoice(
        CashbackCampaign $campaign,
        Invoice $invoice,
        \App\Models\User $user
    ): bool {

        if (
            $campaign->participant_type === 'all'
        ) {
            return true;
        }

        $scopeQuery =
            $campaign->scopes();

        if (
            $campaign->participant_type === 'warehouse'
        ) {

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

        if (
            $campaign->participant_type === 'zone'
        ) {

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

        if (
            $campaign->participant_type === 'branch'
        ) {

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
     * Crear auditoría.
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

            'cashback_campaign_id' =>
            $invoice->cashback_campaign_id,

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

    /**
     * Normalizar número de factura.
     */
    protected function normalizeInvoiceNumber(
        string $numero
    ): string {

        return strtoupper(
            preg_replace(
                '/[^A-Z0-9]/',
                '',
                $numero
            )
        );
    }
}
