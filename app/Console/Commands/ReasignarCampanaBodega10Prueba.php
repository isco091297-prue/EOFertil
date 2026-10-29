<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class ReasignarCampanaBodega10Prueba extends Command
{
    protected $signature = 'eofertil:reasignar-campana-bodega10-prueba
                            {--ejecutar : Confirma la escritura en la base local de pruebas}';

    protected $description = 'Prueba controlada de reasignación de facturas de bodega 10';

    public function handle(): int
    {
        $connection = DB::connection();
        $database = $connection->getDatabaseName();

        if (
            $connection->getDriverName() !== 'mysql' ||
            $database !== 'eofertil_pruebas'
        ) {
            $this->error(
                "Operación cancelada. Base activa: {$database}. " .
                    'Se requiere eofertil_pruebas.'
            );

            return self::FAILURE;
        }

        $this->info("Base verificada: {$database}");

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

                if (!$campaign7 || !$campaign15) {
                    throw new RuntimeException(
                        'No existen las dos campañas requeridas.'
                    );
                }

                if (
                    $campaign7->campaign_type !== 'cashback' ||
                    $campaign15->campaign_type !== 'beneficio_directo'
                ) {
                    throw new RuntimeException(
                        'Los tipos de campaña no coinciden con lo esperado.'
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

                if ($invoices->count() !== 12) {
                    throw new RuntimeException(
                        'Se esperaban 12 facturas; encontradas: ' .
                            $invoices->count()
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
                        'Los estados de las facturas no coinciden.'
                    );
                }

                $invoiceIds = $invoices->pluck('id')->all();

                $movements = DB::table('cashback_transactions')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('cashback_campaign_id', 7)
                    ->orderBy('id')
                    ->lockForUpdate()
                    ->get();

                if ($movements->count() !== 15) {
                    throw new RuntimeException(
                        'Se esperaban 15 movimientos de la campaña 7; ' .
                            'encontrados: ' . $movements->count()
                    );
                }

                $allInvoiceMovements = DB::table('cashback_transactions')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->count();

                if ($allInvoiceMovements !== 15) {
                    throw new RuntimeException(
                        'Hay movimientos adicionales o de otra campaña ' .
                            'vinculados a las facturas seleccionadas.'
                    );
                }

                $admin = DB::table('users')
                    ->where('id', 1)
                    ->where('role_id', 1)
                    ->where('is_active', 1)
                    ->exists();

                if (!$admin) {
                    throw new RuntimeException(
                        'El administrador de prueba ID 1 no es válido.'
                    );
                }

                $userBalancesBefore = DB::table('users')
                    ->whereIn('id', $invoices->pluck('user_id')->unique())
                    ->orderBy('id')
                    ->get([
                        'id',
                        'cashback_total',
                        'cashback_claimed',
                        'cashback_available',
                    ])
                    ->keyBy('id');

                $redemptionBefore = DB::table('cashback_redemptions')
                    ->where('id', 55)
                    ->first();

                if (
                    !$redemptionBefore ||
                    (int) $redemptionBefore->user_id !== 66 ||
                    (float) $redemptionBefore->monto !== 5.79 ||
                    $redemptionBefore->estado !== 'pendiente' ||
                    $redemptionBefore->pagado_at !== null
                ) {
                    throw new RuntimeException(
                        'El canje pendiente de referencia no coincide.'
                    );
                }

                $beforeInvoices = $invoices->keyBy('id')
                    ->map(fn($invoice) => [
                        'campaign_id' => (int) $invoice->cashback_campaign_id,
                        'estado' => $invoice->estado,
                        'cashback_generado' => $invoice->cashback_generado,
                        'porcentaje_cashback' => $invoice->porcentaje_cashback,
                        'total_factura' => $invoice->total_factura,
                        'total_productos_participantes' =>
                        $invoice->total_productos_participantes,
                    ]);

                $beforeMovements = $movements->keyBy('id')
                    ->map(fn($movement) => [
                        'campaign_id' => (int) $movement->cashback_campaign_id,
                        'valor' => $movement->valor,
                        'tipo' => $movement->tipo,
                        'movimiento' => $movement->movimiento,
                        'saldo_despues' => $movement->saldo_despues,
                    ]);

                if (!$this->option('ejecutar')) {
                    return [
                        'modo' => 'simulacion',
                        'facturas' => $invoices->count(),
                        'confirmadas' => $confirmed,
                        'anuladas' => $annulled,
                        'movimientos' => $movements->count(),
                        'usuarios' => $userBalancesBefore->count(),
                        'canje' => (int) $redemptionBefore->id,
                    ];
                }

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

                    DB::table('invoices')
                        ->where('id', $invoice->id)
                        ->where('cashback_campaign_id', 7)
                        ->update([
                            'cashback_campaign_id' => 15,
                            'updated_at' => now(),
                        ]);

                    DB::table('invoice_audits')->insert([
                        'invoice_id' => $invoice->id,
                        'admin_user_id' => 1,
                        'accion' => 'reasignar_campana',
                        'motivo' =>
                        'Prueba local: reasignación a Campaña sur ' .
                            'para bodega 10.',
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

                DB::table('cashback_transactions')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('cashback_campaign_id', 7)
                    ->update([
                        'cashback_campaign_id' => 15,
                        'updated_at' => now(),
                    ]);

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
                            "La verificación de la factura {$id} falló."
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
                            "La verificación del movimiento {$id} falló."
                        );
                    }
                }

                $userBalancesAfter = DB::table('users')
                    ->whereIn('id', $userBalancesBefore->keys()->all())
                    ->orderBy('id')
                    ->get([
                        'id',
                        'cashback_total',
                        'cashback_claimed',
                        'cashback_available',
                    ])
                    ->keyBy('id');

                if (
                    $userBalancesBefore->toJson() !==
                    $userBalancesAfter->toJson()
                ) {
                    throw new RuntimeException(
                        'Los saldos de los usuarios cambiaron.'
                    );
                }

                $redemptionAfter = DB::table('cashback_redemptions')
                    ->where('id', 55)
                    ->first();

                if (
                    !$redemptionAfter ||
                    $redemptionAfter->estado !== 'pendiente' ||
                    (float) $redemptionAfter->monto !== 5.79 ||
                    $redemptionAfter->pagado_at !== null
                ) {
                    throw new RuntimeException(
                        'El canje pendiente cambió inesperadamente.'
                    );
                }

                $auditCount = DB::table('invoice_audits')
                    ->whereIn('invoice_id', $invoiceIds)
                    ->where('admin_user_id', 1)
                    ->where('accion', 'reasignar_campana')
                    ->where('motivo', 'Prueba local: reasignación a Campaña sur para bodega 10.')
                    ->count();

                if ($auditCount !== 12) {
                    throw new RuntimeException(
                        'No se encontraron las 12 auditorías esperadas.'
                    );
                }

                return [
                    'modo' => 'ejecutado_y_verificado',
                    'facturas' => $afterInvoices->count(),
                    'movimientos' => $afterMovements->count(),
                    'auditorias' => $auditCount,
                    'saldos_preservados' => true,
                    'canje_pendiente_preservado' => true,
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
                    'Solo simulación: no se guardaron cambios. ' .
                        'Para ejecutar, usa --ejecutar.'
                );
            } else {
                $this->info(
                    'Operación verificada dentro de eofertil_pruebas.'
                );
            }

            return self::SUCCESS;
        } catch (Throwable $e) {
            report($e);

            $this->error('Operación cancelada: ' . $e->getMessage());
            $this->warn(
                'La transacción fue revertida. No deben quedar cambios parciales.'
            );

            return self::FAILURE;
        }
    }
}
