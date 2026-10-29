<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use RuntimeException;
use Throwable;

class ReasignarCampanaBodega10Produccion extends Command
{
    protected $signature = 'eofertil:reasignar-campana-bodega10-produccion
                            {--ejecutar : Ejecuta la reasignación en producción}';

    protected $description = 'Reasigna las facturas de la bodega 10 de la campaña 7 a la campaña 15, con verificaciones';

    private const FACTURAS_ESPERADAS = [
        497,
        499,
        522,
        536,
        613,
        614,
        661,
        673,
        696,
        739,
        740,
        756,
    ];

    public function handle(): int
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        if (
            $connection->getDriverName() !== 'mysql' ||
            $database !== 'eogreent_eofertil'
        ) {
            $this->error(
                "Operación cancelada. Base activa: {$database}. " .
                    'Se requiere eogreent_eofertil.'
            );

            return self::FAILURE;
        }

        if (
            !Schema::hasTable('invoice_audits') ||
            !Schema::hasTable('cashback_transactions') ||
            !Schema::hasTable('cashback_redemptions')
        ) {
            $this->error(
                'Faltan tablas requeridas. No se realizaron cambios.'
            );

            return self::FAILURE;
        }

        try {
            $result = DB::transaction(function () {
                $campaign7 = DB::table('cashback_campaigns')
                    ->where('id', 7)
                    ->lockForUpdate()
                    ->first();

                $campaign15 = DB::table('cashback_campaigns')
                    ->where('id', 15)
                    ->lockForUpdate()
                    ->first();

                if (
                    !$campaign7 ||
                    !$campaign15 ||
                    $campaign7->campaign_type !== 'cashback' ||
                    $campaign15->campaign_type !== 'beneficio_directo'
                ) {
                    throw new RuntimeException(
                        'Las campañas 7 y 15 no coinciden con lo esperado.'
                    );
                }

                $scope = DB::table('cashback_campaign_scopes')
                    ->where('cashback_campaign_id', 15)
                    ->where('warehouse_id', 10)
                    ->where('required', 1)
                    ->exists();

                if (!$scope) {
                    throw new RuntimeException(
                        'La campaña 15 no tiene asignada la bodega 10.'
                    );
                }

                $invoices = DB::table('invoices as i')
                    ->join('users as u', 'u.id', '=', 'i.user_id')
                    ->whereIn('i.id', self::FACTURAS_ESPERADAS)
                    ->where('i.cashback_campaign_id', 7)
                    ->whereBetween('i.fecha_factura', [
                        '2026-10-01',
                        '2026-10-31',
                    ])
                    ->where('u.warehouse_id', 10)
                    ->select('i.*')
                    ->orderBy('i.id')
                    ->lockForUpdate()
                    ->get();

                $idsEncontrados = $invoices->pluck('id')
                    ->map(fn($id) => (int) $id)
                    ->sort()
                    ->values()
                    ->all();

                $idsEsperados = self::FACTURAS_ESPERADAS;
                sort($idsEsperados);

                if ($idsEncontrados !== $idsEsperados) {
                    throw new RuntimeException(
                        'Las facturas de producción no coinciden con las ' .
                            '12 esperadas. Encontradas: ' .
                            json_encode($idsEncontrados)
                    );
                }

                $confirmed = $invoices->where(
                    'estado',
                    'confirmada'
                )->count();

                $annulled = $invoices->where(
                    'estado',
                    'anulada'
                )->count();

                if ($confirmed !== 11 || $annulled !== 1) {
                    throw new RuntimeException(
                        "Estados inesperados: {$confirmed} confirmadas, " .
                            "{$annulled} anuladas."
                    );
                }

                $invoiceIds = $invoices->pluck('id')->all();

                $movements = DB::table('cashback_transactions')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('cashback_campaign_id', 7)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                $allMovementsCount = DB::table('cashback_transactions')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->count();

                if (
                    $movements->count() !== 15 ||
                    $allMovementsCount !== 15
                ) {
                    throw new RuntimeException(
                        'Los movimientos de producción no coinciden con ' .
                            'los 15 esperados. Campaña 7: ' .
                            $movements->count() .
                            '; movimientos totales: ' . $allMovementsCount
                    );
                }

                $admin = DB::table('users')
                    ->where('role_id', 1)
                    ->where('is_active', 1)
                    ->orderBy('id')
                    ->first();

                if (!$admin) {
                    throw new RuntimeException(
                        'No existe un administrador activo con role_id 1.'
                    );
                }

                $userIds = $invoices->pluck('user_id')
                    ->unique()
                    ->sort()
                    ->values()
                    ->all();

                $balancesBefore = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->orderBy('id')
                    ->get([
                        'id',
                        'cashback_total',
                        'cashback_claimed',
                        'cashback_available',
                    ])
                    ->keyBy('id');

                $redemptionsBefore = DB::table('cashback_redemptions')
                    ->whereIn('user_id', $userIds)
                    ->orderBy('id')
                    ->get()
                    ->toJson();

                $beforeInvoices = $invoices->keyBy('id')->map(
                    fn($invoice) => [
                        'estado' => $invoice->estado,
                        'cashback_generado' => $invoice->cashback_generado,
                        'porcentaje_cashback' => $invoice->porcentaje_cashback,
                        'total_factura' => $invoice->total_factura,
                        'total_productos_participantes' =>
                        $invoice->total_productos_participantes,
                    ]
                );

                $beforeMovements = $movements->keyBy('id')->map(
                    fn($movement) => [
                        'valor' => $movement->valor,
                        'tipo' => $movement->tipo,
                        'movimiento' => $movement->movimiento,
                        'saldo_despues' => $movement->saldo_despues,
                    ]
                );

                if (!$this->option('ejecutar')) {
                    return [
                        'modo' => 'simulacion',
                        'base' => DB::connection()->getDatabaseName(),
                        'campana_origen' => 7,
                        'campana_destino' => 15,
                        'bodega' => 10,
                        'facturas' => $invoices->count(),
                        'confirmadas' => $confirmed,
                        'anuladas' => $annulled,
                        'movimientos' => $movements->count(),
                        'usuarios' => $balancesBefore->count(),
                        'administrador' => (int) $admin->id,
                    ];
                }

                $motivo = 'Reasignación de facturas de bodega 10 ' .
                    'de campaña 7 a campaña 15.';

                foreach ($invoices as $invoice) {
                    $datosAnteriores = [
                        'cashback_campaign_id' =>
                        (int) $invoice->cashback_campaign_id,
                        'estado' => $invoice->estado,
                        'cashback_generado' => $invoice->cashback_generado,
                        'porcentaje_cashback' => $invoice->porcentaje_cashback,
                    ];

                    $datosNuevos = $datosAnteriores;
                    $datosNuevos['cashback_campaign_id'] = 15;

                    $updated = DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->where('cashback_campaign_id', 7)
                        ->update([
                            'cashback_campaign_id' => 15,
                            'updated_at' => now(),
                        ]);

                    if ($updated !== 1) {
                        throw new RuntimeException(
                            "No se pudo actualizar la factura {$invoice->id}."
                        );
                    }

                    DB::table('invoice_audits')->insert([
                        'invoice_id' => $invoice->id,
                        'admin_user_id' => $admin->id,
                        'accion' => 'reasignar_campana',
                        'motivo' => $motivo,
                        'estado_anterior' => $invoice->estado,
                        'estado_nuevo' => $invoice->estado,
                        'datos_anteriores' => json_encode(
                            $datosAnteriores,
                            JSON_THROW_ON_ERROR
                        ),
                        'datos_nuevos' => json_encode(
                            $datosNuevos,
                            JSON_THROW_ON_ERROR
                        ),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                $updatedMovements = DB::table('cashback_transactions')
                    ->whereIn('id', $beforeMovements->keys()->all())
                    ->where('cashback_campaign_id', 7)
                    ->update([
                        'cashback_campaign_id' => 15,
                        'updated_at' => now(),
                    ]);

                if ($updatedMovements !== 15) {
                    throw new RuntimeException(
                        "Se actualizaron {$updatedMovements} movimientos; " .
                            'se esperaban 15.'
                    );
                }

                $afterInvoices = DB::table('invoices')
                    ->whereIn('id', $invoiceIds)
                    ->orderBy('id')
                    ->get()
                    ->keyBy('id');

                foreach ($beforeInvoices as $id => $before) {
                    $after = $afterInvoices->get($id);

                    if (
                        !$after ||
                        (int) $after->cashback_campaign_id !== 15 ||
                        $after->estado !== $before['estado'] ||
                        (string) $after->cashback_generado !==
                        (string) $before['cashback_generado'] ||
                        (string) $after->porcentaje_cashback !==
                        (string) $before['porcentaje_cashback'] ||
                        (string) $after->total_factura !==
                        (string) $before['total_factura'] ||
                        (string) $after->total_productos_participantes !==
                        (string) $before['total_productos_participantes']
                    ) {
                        throw new RuntimeException(
                            "Falló la verificación de la factura {$id}."
                        );
                    }
                }

                $afterMovements = DB::table('cashback_transactions')
                    ->whereIn('id', $beforeMovements->keys()->all())
                    ->orderBy('id')
                    ->get()
                    ->keyBy('id');

                foreach ($beforeMovements as $id => $before) {
                    $after = $afterMovements->get($id);

                    if (
                        !$after ||
                        (int) $after->cashback_campaign_id !== 15 ||
                        (string) $after->valor !== (string) $before['valor'] ||
                        $after->tipo !== $before['tipo'] ||
                        $after->movimiento !== $before['movimiento'] ||
                        (string) $after->saldo_despues !==
                        (string) $before['saldo_despues']
                    ) {
                        throw new RuntimeException(
                            "Falló la verificación del movimiento {$id}."
                        );
                    }
                }

                $balancesAfter = DB::table('users')
                    ->whereIn('id', $userIds)
                    ->orderBy('id')
                    ->get([
                        'id',
                        'cashback_total',
                        'cashback_claimed',
                        'cashback_available',
                    ])
                    ->keyBy('id');

                if ($balancesBefore->toJson() !== $balancesAfter->toJson()) {
                    throw new RuntimeException(
                        'Los saldos de los usuarios cambiaron.'
                    );
                }

                $redemptionsAfter = DB::table('cashback_redemptions')
                    ->whereIn('user_id', $userIds)
                    ->orderBy('id')
                    ->get()
                    ->toJson();

                if ($redemptionsBefore !== $redemptionsAfter) {
                    throw new RuntimeException(
                        'Los canjes de los usuarios cambiaron.'
                    );
                }

                $auditCount = DB::table('invoice_audits')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('admin_user_id', $admin->id)
                    ->where('accion', 'reasignar_campana')
                    ->where('motivo', $motivo)
                    ->count();

                if ($auditCount !== 12) {
                    throw new RuntimeException(
                        "Se encontraron {$auditCount} auditorías; " .
                            'se esperaban 12.'
                    );
                }

                return [
                    'modo' => 'ejecutado_y_verificado',
                    'base' => DB::connection()->getDatabaseName(),
                    'facturas' => $afterInvoices->count(),
                    'movimientos' => $afterMovements->count(),
                    'auditorias' => $auditCount,
                    'saldos_preservados' => true,
                    'canjes_preservados' => true,
                ];
            }, 1);

            $this->newLine();
            $this->info('Resultado:');
            $this->line(json_encode(
                $result,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
            ));

            if ($result['modo'] === 'simulacion') {
                $this->warn(
                    'SIMULACIÓN: no se guardaron cambios. ' .
                        'Revisa el resultado antes de usar --ejecutar.'
                );
            } else {
                $this->info(
                    'Reasignación completada y verificada en producción.'
                );
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);

            $this->error('Operación cancelada: ' . $e->getMessage());
            $this->warn(
                'La transacción se revirtió. No deben quedar cambios parciales.'
            );

            return self::FAILURE;
        }
    }
}
