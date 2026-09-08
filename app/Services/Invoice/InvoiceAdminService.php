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
     * ================================================================
     * APROBAR FACTURA
     * ================================================================
     *
     * Una factura solamente puede aprobarse mientras esté pendiente.
     *
     * procesando
     *      ↓
     * aprobar
     *      ↓
     * confirmada
     *
     * Al aprobar:
     *
     * - Se utiliza la campaña guardada en la factura.
     * - Se calcula el cashback.
     * - Se acredita el cashback al usuario.
     * - Se registra la transacción.
     * - Se reconstruye el acumulado/ranking.
     * - Se registra la auditoría.
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


            /*
            |--------------------------------------------------------------------------
            | Solo una factura pendiente puede aprobarse
            |--------------------------------------------------------------------------
            */

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
            | Generar y acreditar cashback
            |--------------------------------------------------------------------------
            |
            | CashbackService utiliza la campaña vinculada a la factura.
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
     * ================================================================
     * MODIFICAR FACTURA
     * ================================================================
     *
     * IMPORTANTE:
     *
     * SOLO se puede modificar una factura que todavía esté
     * en estado "procesando".
     *
     * La modificación NO aprueba la factura.
     *
     * Flujo:
     *
     * procesando
     *      ↓
     * modificar
     *      ↓
     * actualizar valores de productos
     *      ↓
     * recalcular total participante
     *      ↓
     * recalcular cashback
     *      ↓
     * sigue procesando
     *
     *
     * El administrador NO modifica:
     *
     * - número de factura
     * - fecha
     * - total general de factura
     * - campaña
     * - porcentaje de campaña
     * - estado
     * - cashback manualmente
     *
     * Solamente modifica los valores de los productos.
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


            /*
            |--------------------------------------------------------------------------
            | Solo se pueden modificar facturas pendientes
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Snapshot anterior
            |--------------------------------------------------------------------------
            */

            $estadoAnterior = $invoice->estado;

            $datosAnteriores = $this->invoiceSnapshot(
                $invoice
            );


            /*
            |--------------------------------------------------------------------------
            | Validar productos
            |--------------------------------------------------------------------------
            |
            | El formulario solamente debe enviar valores correspondientes
            | a los invoice_items existentes.
            |
            */

            $itemsData = $data['items'] ?? [];


            if (!is_array($itemsData)) {

                throw new RuntimeException(
                    'Los valores de los productos no son válidos.'
                );
            }


            /*
            |--------------------------------------------------------------------------
            | Actualizar únicamente valores de productos
            |--------------------------------------------------------------------------
            */

            foreach ($itemsData as $itemId => $itemData) {

                $item = $invoice->items
                    ->firstWhere(
                        'id',
                        (int) $itemId
                    );


                /*
                |--------------------------------------------------------------
                | Si llega un producto que no pertenece a la factura,
                | rechazamos la operación.
                |--------------------------------------------------------------
                */

                if (!$item) {

                    throw new RuntimeException(
                        'Se intentó modificar un producto que no pertenece a esta factura.'
                    );
                }


                if (
                    !isset($itemData['valor'])
                    || !is_numeric($itemData['valor'])
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


                $item->valor = $valor;

                $item->save();
            }


            /*
            |--------------------------------------------------------------------------
            | Recalcular automáticamente total participante
            |--------------------------------------------------------------------------
            |
            | El administrador NO escribe este valor.
            |
            | El sistema suma todos los productos registrados.
            |
            */

            $totalParticipantes = round(
                (float) $invoice->items()->sum('valor'),
                2
            );


            $invoice->total_productos_participantes =
                $totalParticipantes;


            /*
            |--------------------------------------------------------------------------
            | La factura continúa pendiente
            |--------------------------------------------------------------------------
            */

            $invoice->estado = 'procesando';

            /*
            |--------------------------------------------------------------------------
            | Limpiar valores financieros anteriores
            |--------------------------------------------------------------------------
            |
            | Si la factura estaba pendiente, este valor únicamente
            | representaba un cálculo anterior.
            |
            | Todavía NO existe una acreditación financiera.
            |
            */

            $invoice->cashback_generado = 0;

            $invoice->porcentaje_cashback = 0;


            $invoice->save();


            /*
            |--------------------------------------------------------------------------
            | Recalcular cashback sin acreditar
            |--------------------------------------------------------------------------
            |
            | CashbackService:
            |
            | - utiliza la campaña ORIGINAL de la factura;
            | - calcula el porcentaje;
            | - guarda el cashback calculado;
            | - NO modifica el saldo;
            | - NO crea transacciones;
            | - mantiene la factura procesando.
            |
            */

            $invoice = $this->cashbackService
                ->recalculatePending(
                    $invoice->fresh()
                );


            /*
            |--------------------------------------------------------------------------
            | Reconstruir acumulado / ranking
            |--------------------------------------------------------------------------
            |
            | Las facturas procesando NO cuentan.
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
     * ================================================================
     * ANULAR FACTURA
     * ================================================================
     *
     * IMPORTANTE:
     *
     * La anulación administrativa solamente puede realizarse
     * mientras la factura esté pendiente.
     *
     * procesando
     *      ↓
     * anular
     *      ↓
     * anulada
     *
     * Una factura confirmada ya no tiene acciones administrativas
     * disponibles.
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


            /*
            |--------------------------------------------------------------------------
            | Solo se puede anular una factura pendiente
            |--------------------------------------------------------------------------
            */

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

            $datosAnteriores = $this->invoiceSnapshot(
                $invoice
            );


            /*
            |--------------------------------------------------------------------------
            | Una factura pendiente no tiene cashback acreditado
            |--------------------------------------------------------------------------
            |
            | Por seguridad dejamos sus valores financieros en cero.
            |
            */

            $invoice->cashback_generado = 0;

            $invoice->porcentaje_cashback = 0;

            $invoice->estado = 'anulada';

            $invoice->save();


            /*
            |--------------------------------------------------------------------------
            | Reconstruir acumulado / ranking
            |--------------------------------------------------------------------------
            |
            | Una factura anulada nunca debe contar.
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
     * ================================================================
     * RECONSTRUIR RANKINGS
     * ================================================================
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
     * ================================================================
     * RECONSTRUCCIÓN INTERNA DE RANKINGS
     * ================================================================
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
        | Obtener campañas relevantes
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
            | Facturas válidas para esta campaña
            |--------------------------------------------------------------------------
            |
            | MUY IMPORTANTE:
            |
            | La factura debe:
            |
            | - pertenecer al usuario;
            | - pertenecer a esta campaña;
            | - estar confirmada;
            | - estar dentro del período;
            | - cumplir el alcance de la campaña.
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
                |--------------------------------------------------------------------------
                | Acumulado de ventas
                |--------------------------------------------------------------------------
                */

                $salesTotal +=
                    (float) $invoice
                        ->total_productos_participantes;


                /*
                |--------------------------------------------------------------------------
                | Cashback acumulado
                |--------------------------------------------------------------------------
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
            | Si ya no existen facturas válidas
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


            /*
            |--------------------------------------------------------------------------
            | La posición se recalcula posteriormente por el sistema
            |--------------------------------------------------------------------------
            */

            $ranking->position = 0;

            $ranking->save();
        }
    }


    /**
     * ================================================================
     * VALIDAR ALCANCE DE CAMPAÑA
     * ================================================================
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


        /*
        |--------------------------------------------------------------------------
        | Warehouse
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Zone
        |--------------------------------------------------------------------------
        */

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


        /*
        |--------------------------------------------------------------------------
        | Branch
        |--------------------------------------------------------------------------
        */

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
     * ================================================================
     * CREAR AUDITORÍA
     * ================================================================
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
     * ================================================================
     * SNAPSHOT DE FACTURA
     * ================================================================
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


            /*
            |--------------------------------------------------------------------------
            | Campaña ORIGINAL
            |--------------------------------------------------------------------------
            */

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


            /*
            |--------------------------------------------------------------------------
            | Productos
            |--------------------------------------------------------------------------
            */

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
     * ================================================================
     * NORMALIZAR NÚMERO DE FACTURA
     * ================================================================
     *
     * Se mantiene por compatibilidad con registros existentes.
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
