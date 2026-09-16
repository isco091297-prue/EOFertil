<?php

namespace App\Services\Invoice;

use App\Models\CashbackCampaign;
use App\Models\CampaignUserRanking;
use App\Models\Invoice;
use App\Models\InvoiceAudit;
use App\Models\User;
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
     * Aprobar factura.
     *
     * El cashback YA fue acreditado al registrar.
     *
     * Aprobar solamente cambia:
     *
     * procesando → confirmada
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
                if ($invoice->estado === 'confirmada') {
                    throw new RuntimeException(
                        'La factura ya fue confirmada y solo puede ser consultada.'
                    );
                }

                if ($invoice->estado === 'anulada') {
                    throw new RuntimeException(
                        'La factura está anulada.'
                    );
                }

                throw new RuntimeException(
                    'La factura no puede ser aprobada.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot($invoice);

            /*
            |--------------------------------------------------------------------------
            | El cashback ya fue acreditado.
            |--------------------------------------------------------------------------
            */

            $invoice->estado = 'confirmada';

            $invoice->save();

            /*
            |--------------------------------------------------------------------------
            | Reconstruir ranking/acumulado
            |--------------------------------------------------------------------------
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            $invoice->refresh();

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
     * Modificar factura pendiente.
     *
     * El cashback anterior ya fue entregado.
     *
     * Por eso:
     *
     * 1. Revierte cashback anterior.
     * 2. Cambia valores.
     * 3. Calcula nuevo cashback.
     * 4. Acredita nuevo cashback.
     * 5. Mantiene procesando.
     * 6. Reconstruye ranking.
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

            if ($invoice->estado !== 'procesando') {
                if ($invoice->estado === 'confirmada') {
                    throw new RuntimeException(
                        'La factura ya fue confirmada y solo puede ser consultada.'
                    );
                }

                if ($invoice->estado === 'anulada') {
                    throw new RuntimeException(
                        'Una factura anulada no puede modificarse.'
                    );
                }

                throw new RuntimeException(
                    'La factura no puede ser modificada.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot($invoice);

            $itemsData = $data['items'] ?? [];

            if (!is_array($itemsData)) {
                throw new RuntimeException(
                    'Los valores de los productos no son válidos.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Verificar que lleguen todos los productos
            |--------------------------------------------------------------------------
            */

            $invoiceItemIds = $invoice->items
                ->pluck('id')
                ->map(fn($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            $submittedItemIds = collect(array_keys($itemsData))
                ->map(fn($id) => (int) $id)
                ->sort()
                ->values()
                ->all();

            if ($invoiceItemIds !== $submittedItemIds) {
                throw new RuntimeException(
                    'Debe enviarse el valor de todos los productos de la factura.'
                );
            }

            /*
            |--------------------------------------------------------------------------
            | Validar valores
            |--------------------------------------------------------------------------
            */

            foreach ($itemsData as $itemId => $itemData) {
                $item = $invoice->items
                    ->firstWhere(
                        'id',
                        (int) $itemId
                    );

                if (!$item) {
                    throw new RuntimeException(
                        'Se intentó modificar un producto que no pertenece a esta factura.'
                    );
                }

                if (
                    !isset($itemData['valor']) ||
                    !is_numeric($itemData['valor'])
                ) {
                    throw new RuntimeException(
                        'El valor de uno de los productos no es válido.'
                    );
                }

                $valor = round(
                    (float) $itemData['valor'],
                    2
                );

                if ($valor < 0) {
                    throw new RuntimeException(
                        'El valor de un producto no puede ser negativo.'
                    );
                }
            }

            /*
            |--------------------------------------------------------------------------
            | Revertir cashback que ya recibió el usuario
            |--------------------------------------------------------------------------
            */

            $this->cashbackService
                ->reverseInvoiceCashbackOnlyInternal(
                    $invoice
                );

            /*
            |--------------------------------------------------------------------------
            | Actualizar productos
            |--------------------------------------------------------------------------
            */

            foreach ($itemsData as $itemId => $itemData) {
                $item = $invoice->items
                    ->firstWhere(
                        'id',
                        (int) $itemId
                    );

                $item->valor = round(
                    (float) $itemData['valor'],
                    2
                );

                $item->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Recalcular totales
            |--------------------------------------------------------------------------
            */

            $totalParticipantes = round(
                (float) $invoice->items()->sum('valor'),
                2
            );

            $invoice->total_factura = $totalParticipantes;

            $invoice->total_productos_participantes =
                $totalParticipantes;

            $invoice->save();

            /*
            |--------------------------------------------------------------------------
            | Acreditar el nuevo cashback
            |--------------------------------------------------------------------------
            */

            $invoice = $this->cashbackService
                ->creditCorrectedCashbackInternal(
                    $invoice->fresh()
                );

            /*
            |--------------------------------------------------------------------------
            | Reconstruir ranking/acumulado
            |--------------------------------------------------------------------------
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            $invoice->refresh();

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
     * Anular factura.
     */
    public function annul(
        Invoice $invoice,
        int $adminUserId,
        ?string $motivo = null
    ): Invoice {
        if (!$motivo || trim($motivo) === '') {
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

            if ($invoice->estado !== 'procesando') {
                if ($invoice->estado === 'confirmada') {
                    throw new RuntimeException(
                        'La factura ya fue confirmada y solo puede ser consultada.'
                    );
                }

                if ($invoice->estado === 'anulada') {
                    throw new RuntimeException(
                        'La factura ya está anulada.'
                    );
                }

                throw new RuntimeException(
                    'La factura no puede ser anulada.'
                );
            }

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot($invoice);

            /*
            |--------------------------------------------------------------------------
            | Revertir cashback y bono
            |--------------------------------------------------------------------------
            */

            $this->cashbackService
                ->reverseInvoiceInternal(
                    $invoice,
                    trim($motivo)
                );

            /*
            |--------------------------------------------------------------------------
            | Anular factura
            |--------------------------------------------------------------------------
            */

            $invoice->cashback_generado = 0;

            $invoice->porcentaje_cashback = 0;

            $invoice->estado = 'anulada';

            $invoice->save();

            /*
            |--------------------------------------------------------------------------
            | Reconstruir ranking/acumulado
            |--------------------------------------------------------------------------
            */

            $this->rebuildRankingsForUserInternal(
                $invoice->user_id
            );

            $invoice->refresh();

            $this->createAudit(
                invoice: $invoice,
                adminUserId: $adminUserId,
                accion: 'anular',
                motivo: trim($motivo),
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
     * Reconstruir completamente una campaña.
     *
     * Se utiliza cuando se eliminan o limpian
     * datos de un usuario.
     *
     * Las campañas ya procesadas representan
     * resultados históricos y no se recalculan.
     */
    public function rebuildRankingsForCampaign(
        int $campaignId
    ): void {
        DB::transaction(function () use ($campaignId) {
            $campaign = CashbackCampaign::query()
                ->with('scopes')
                ->lockForUpdate()
                ->findOrFail($campaignId);

            /*
            |--------------------------------------------------------------------------
            | Las campañas procesadas son históricas.
            |--------------------------------------------------------------------------
            |
            | No bloqueamos la limpieza/eliminación del usuario.
            | Simplemente no modificamos el ranking histórico.
            |--------------------------------------------------------------------------
            */

            if ($campaign->ranking_processed) {
                return;
            }

            $isCashbackRanking =
                $campaign->campaign_type === 'cashback' &&
                $campaign->ranking_enabled;

            $isAccumulatedRanking =
                $campaign->campaign_type === 'ranking_accumulated';

            if (
                !$isCashbackRanking &&
                !$isAccumulatedRanking
            ) {
                CampaignUserRanking::query()
                    ->where(
                        'cashback_campaign_id',
                        $campaign->id
                    )
                    ->delete();

                return;
            }

            /*
            |--------------------------------------------------------------------------
            | Eliminar el ranking almacenado de esta campaña.
            |--------------------------------------------------------------------------
            */

            CampaignUserRanking::query()
                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )
                ->delete();

            /*
            |--------------------------------------------------------------------------
            | Obtener todas las facturas válidas.
            |--------------------------------------------------------------------------
            |
            | Tanto "procesando" como "confirmada" cuentan.
            | "anulada" queda fuera.
            |--------------------------------------------------------------------------
            */

            $invoiceQuery = Invoice::query()
                ->with([
                    'user',
                    'branch',
                ])
                ->whereIn(
                    'estado',
                    [
                        'procesando',
                        'confirmada',
                    ]
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
                );

            if ($isCashbackRanking) {
                $invoiceQuery->where(
                    'cashback_campaign_id',
                    $campaign->id
                );
            }

            $invoices = $invoiceQuery
                ->orderBy('id')
                ->get();

            /*
            |--------------------------------------------------------------------------
            | Acumular por usuario.
            |--------------------------------------------------------------------------
            */

            $participants = [];

            foreach ($invoices as $invoice) {
                if (!$invoice->user) {
                    continue;
                }

                if (
                    !$this->campaignAppliesToInvoice(
                        $campaign,
                        $invoice,
                        $invoice->user
                    )
                ) {
                    continue;
                }

                $userId = (int) $invoice->user_id;

                if (!isset($participants[$userId])) {
                    $participants[$userId] = [
                        'user' => $invoice->user,
                        'warehouse_id' =>
                        $invoice->user->warehouse_id,
                        'zone_id' =>
                        $invoice->user->zone_id,
                        'branch_id' =>
                        $invoice->user->branch_id,
                        'sales_total' => 0.0,
                        'cashback_total' => 0.0,
                        'invoice_count' => 0,
                    ];
                }

                $participants[$userId]['sales_total'] +=
                    (float) $invoice->total_productos_participantes;

                if ($isCashbackRanking) {
                    $participants[$userId]['cashback_total'] +=
                        (float) $invoice->cashback_generado;
                }

                $participants[$userId]['invoice_count']++;
            }

            /*
            |--------------------------------------------------------------------------
            | Crear nuevamente los registros de ranking.
            |--------------------------------------------------------------------------
            */

            foreach ($participants as $userId => $participant) {
                $ranking = new CampaignUserRanking();

                $ranking->cashback_campaign_id =
                    $campaign->id;

                $ranking->user_id =
                    $userId;

                $ranking->warehouse_id =
                    $participant['warehouse_id'];

                $ranking->zone_id =
                    $participant['zone_id'];

                $ranking->branch_id =
                    $participant['branch_id'];

                $ranking->sales_total =
                    round(
                        $participant['sales_total'],
                        2
                    );

                $ranking->cashback_total =
                    round(
                        $participant['cashback_total'],
                        2
                    );

                $ranking->invoice_count =
                    $participant['invoice_count'];

                $ranking->position = 0;

                $ranking->save();
            }

            /*
            |--------------------------------------------------------------------------
            | Calcular posiciones.
            |--------------------------------------------------------------------------
            */

            $rankings = CampaignUserRanking::query()
                ->where(
                    'cashback_campaign_id',
                    $campaign->id
                )
                ->get();

            if ($isAccumulatedRanking) {
                $rankings = $rankings
                    ->sort(function ($a, $b) {
                        $salesA =
                            (int) round(
                                ((float) $a->sales_total) * 100
                            );

                        $salesB =
                            (int) round(
                                ((float) $b->sales_total) * 100
                            );

                        if ($salesA !== $salesB) {
                            return $salesB <=> $salesA;
                        }

                        $invoiceComparison =
                            $b->invoice_count
                            <=> $a->invoice_count;

                        if ($invoiceComparison !== 0) {
                            return $invoiceComparison;
                        }

                        return $a->user_id
                            <=> $b->user_id;
                    })
                    ->values();
            } else {
                $rankings = $rankings
                    ->sort(function ($a, $b) {
                        $cashbackA =
                            (int) round(
                                ((float) $a->cashback_total) * 100
                            );

                        $cashbackB =
                            (int) round(
                                ((float) $b->cashback_total) * 100
                            );

                        if ($cashbackA !== $cashbackB) {
                            return $cashbackB <=> $cashbackA;
                        }

                        $salesA =
                            (int) round(
                                ((float) $a->sales_total) * 100
                            );

                        $salesB =
                            (int) round(
                                ((float) $b->sales_total) * 100
                            );

                        if ($salesA !== $salesB) {
                            return $salesB <=> $salesA;
                        }

                        $invoiceComparison =
                            $a->invoice_count
                            <=> $b->invoice_count;

                        if ($invoiceComparison !== 0) {
                            return $invoiceComparison;
                        }

                        return $a->user_id
                            <=> $b->user_id;
                    })
                    ->values();
            }

            /*
            |--------------------------------------------------------------------------
            | Guardar posiciones.
            |--------------------------------------------------------------------------
            */

            foreach ($rankings as $index => $ranking) {
                $ranking->position = $index + 1;

                $ranking->save();
            }
        });
    }

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
            $campaignIdsFromRankings->isEmpty() &&
            $userInvoiceDates->isEmpty()
        ) {
            return;
        }

        $campaigns =
            CashbackCampaign::query()
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

        $user = User::findOrFail(
            $userId
        );

        foreach ($campaigns as $campaign) {
            $isCashbackRanking =
                $campaign->campaign_type === 'cashback'
                &&
                $campaign->ranking_enabled;

            $isAccumulatedRanking =
                $campaign->campaign_type ===
                'ranking_accumulated';

            if (
                !$isCashbackRanking
                &&
                !$isAccumulatedRanking
            ) {
                continue;
            }

            $invoiceQuery = Invoice::query()
                ->with([
                    'user',
                    'branch',
                ])
                ->where(
                    'user_id',
                    $userId
                )
                ->whereIn(
                    'estado',
                    [
                        'procesando',
                        'confirmada',
                    ]
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
                );

            if ($isCashbackRanking) {
                $invoiceQuery->where(
                    'cashback_campaign_id',
                    $campaign->id
                );
            }

            $invoices = $invoiceQuery
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

                $salesTotal +=
                    (float) $invoice
                        ->total_productos_participantes;

                if ($isCashbackRanking) {
                    $cashbackTotal +=
                        (float) $invoice
                            ->cashback_generado;
                }

                $invoiceCount++;
            }

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

            $ranking =
                CampaignUserRanking::query()
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

    protected function campaignAppliesToInvoice(
        CashbackCampaign $campaign,
        Invoice $invoice,
        User $user
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
