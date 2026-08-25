<?php

namespace App\Services\Invoice;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\CashbackTransaction;
use App\Models\CashbackCampaign;
use App\Models\Product;
use App\Models\User;
use App\Models\InvoiceItem;
use App\Services\Cashback\CashbackService;
use Exception;
use App\Services\Ranking\RankingCalculatorService;

class InvoiceService
{
    public function __construct(
        protected CashbackService $cashbackService,
        private readonly RankingCalculatorService $rankingCalculatorService,
    ) {}

    /**
     * Registrar una factura.
     *
     * @throws Exception
     */
    public function store(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {

            $this->validateData($data);

            /*
            |--------------------------------------------------------------------------
            | Crear factura
            |--------------------------------------------------------------------------
            */

            $invoice = $this->createInvoice($data);

            /*
            |--------------------------------------------------------------------------
            | Crear productos de la factura
            |--------------------------------------------------------------------------
            */

            $totales = $this->createItems(
                $invoice,
                $data['items']
            );

            /*
            |--------------------------------------------------------------------------
            | Actualizar totales
            |--------------------------------------------------------------------------
            */

            $this->updateInvoiceTotals(
                $invoice,
                $totales
            );

            /*
            |--------------------------------------------------------------------------
            | Generar cashback
            |--------------------------------------------------------------------------
            |
            | Solo la campaña de tipo cashback genera saldo.
            | Las campañas de ranking acumulado no generan cashback.
            |
            */

            $this->cashbackService->generate(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Procesar rankings
            |--------------------------------------------------------------------------
            |
            | La misma factura puede participar:
            |
            | - En el ranking Cashback.
            | - En el ranking acumulado.
            |
            | RankingCalculatorService se encarga de determinar
            | qué campañas aplican.
            |
            */

            $this->rankingCalculatorService->process(
                $invoice
            );

            /*
            |--------------------------------------------------------------------------
            | Recargar factura
            |--------------------------------------------------------------------------
            */

            $updatedInvoice = $invoice->fresh([
                'cashbackCampaign:id,nombre',
                'branch:id,name',
                'items.product:id,name',
            ]);

            /*
            |--------------------------------------------------------------------------
            | Primera factura
            |--------------------------------------------------------------------------
            */

            $firstInvoiceBonus = CashbackTransaction::where(
                'user_id',
                $invoice->user_id
            )
                ->where(
                    'invoice_id',
                    $invoice->id
                )
                ->where(
                    'tipo',
                    'bonificacion'
                )
                ->where(
                    'descripcion',
                    'Bono por registrar tu primera factura'
                )
                ->first();

            $updatedInvoice->setAttribute(
                'logro_primera_factura',
                $firstInvoiceBonus !== null
            );

            $updatedInvoice->setAttribute(
                'bono_primera_factura',
                $firstInvoiceBonus
                    ? (float) $firstInvoiceBonus->valor
                    : 0
            );

            return $updatedInvoice;
        });
    }

    /**
     * Validar datos.
     *
     * @throws Exception
     */
    private function validateData(array $data): void
    {
        /*
        |--------------------------------------------------------------------------
        | Usuario
        |--------------------------------------------------------------------------
        */

        if (
            !isset($data['user_id']) ||
            !is_numeric($data['user_id'])
        ) {
            throw new Exception(
                'No fue posible identificar el usuario autenticado.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Sucursal
        |--------------------------------------------------------------------------
        */

        if (
            !isset($data['branch_id']) ||
            !is_numeric($data['branch_id'])
        ) {
            throw new Exception(
                'No fue posible identificar la sucursal del usuario.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Campaña
        |--------------------------------------------------------------------------
        */

        if (empty($data['cashback_campaign_id'])) {
            throw new Exception(
                'Debe indicar la campaña de cashback.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Productos
        |--------------------------------------------------------------------------
        */

        $productos = [];

        foreach ($data['items'] as $index => $item) {

            if (empty($item['product_id'])) {
                throw new Exception(
                    'El producto de la fila ' .
                        ($index + 1) .
                        ' es obligatorio.'
                );
            }

            if (!isset($item['valor'])) {
                throw new Exception(
                    'Debe ingresar el valor del producto en la fila ' .
                        ($index + 1) .
                        '.'
                );
            }

            if (!is_numeric($item['valor'])) {
                throw new Exception(
                    'El valor del producto en la fila ' .
                        ($index + 1) .
                        ' es inválido.'
                );
            }

            if ($item['valor'] <= 0) {
                throw new Exception(
                    'El valor del producto en la fila ' .
                        ($index + 1) .
                        ' debe ser mayor que cero.'
                );
            }

            if (in_array($item['product_id'], $productos)) {
                throw new Exception(
                    'No puede registrar el mismo producto dos veces en la misma factura.'
                );
            }

            $productos[] = $item['product_id'];
        }

        /*
        |--------------------------------------------------------------------------
        | Validar usuario
        |--------------------------------------------------------------------------
        */

        $user = User::find($data['user_id']);

        if (!$user) {
            throw new Exception(
                'El usuario no existe.'
            );
        }

        if (!$user->is_active) {
            throw new Exception(
                'El usuario se encuentra inactivo.'
            );
        }

        if ($user->branch_id !== $data['branch_id']) {
            throw new Exception(
                'El usuario no pertenece a la sucursal seleccionada.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar sucursal
        |--------------------------------------------------------------------------
        */

        $branch = Branch::find($data['branch_id']);

        if (!$branch) {
            throw new Exception(
                'La sucursal no existe.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar campaña
        |--------------------------------------------------------------------------
        */

        $campaign = CashbackCampaign::find(
            $data['cashback_campaign_id']
        );

        if (!$campaign) {
            throw new Exception(
                'La campaña de cashback no existe.'
            );
        }

        if (!$campaign->activo) {
            throw new Exception(
                'La campaña de cashback está inactiva.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar porcentaje
        |--------------------------------------------------------------------------
        |
        | Las campañas Cashback necesitan porcentaje.
        |
        | Las campañas ranking_accumulated NO necesitan porcentaje,
        | porque su objetivo es acumular ventas para determinar
        | el ganador.
        |
        */

        if (
            $campaign->campaign_type === 'cashback' &&
            $campaign->porcentaje <= 0
        ) {
            throw new Exception(
                'La campaña de cashback no tiene un porcentaje válido.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar vigencia
        |--------------------------------------------------------------------------
        */

        $today = now()->startOfDay();

        if ($today->lt($campaign->fecha_inicio)) {
            throw new Exception(
                'La campaña de cashback aún no ha iniciado.'
            );
        }

        if ($today->gt($campaign->fecha_fin)) {
            throw new Exception(
                'La campaña de cashback ya finalizó.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar factura repetida
        |--------------------------------------------------------------------------
        */

        $invoiceExists = Invoice::where(
            'branch_id',
            $data['branch_id']
        )
            ->where(
                'numero_factura_original',
                $data['numero_factura_original']
            )
            ->exists();

        if ($invoiceExists) {
            throw new Exception(
                'Ya existe una factura registrada con ese número en esta sucursal.'
            );
        }

        /*
        |--------------------------------------------------------------------------
        | Validar productos existentes y activos
        |--------------------------------------------------------------------------
        */

        $products = Product::whereIn(
            'id',
            array_column(
                $data['items'],
                'product_id'
            )
        )
            ->get()
            ->keyBy('id');

        foreach ($data['items'] as $item) {

            $product = $products[$item['product_id']] ?? null;

            if (!$product) {
                throw new Exception(
                    'Uno de los productos no existe.'
                );
            }

            if (!$product->is_active) {
                throw new Exception(
                    "El producto {$product->name} se encuentra inactivo."
                );
            }
        }
    }

    /**
     * Crear factura.
     */
    private function createInvoice(array $data): Invoice
    {
        $campaign = CashbackCampaign::findOrFail(
            $data['cashback_campaign_id']
        );

        return Invoice::create([
            'cashback_campaign_id' => $campaign->id,

            'user_id' => $data['user_id'],

            'branch_id' => $data['branch_id'],

            'numero_factura_original' =>
            $data['numero_factura_original'],

            'numero_factura_normalizado' => preg_replace(
                '/\D/',
                '',
                $data['numero_factura_original']
            ),

            'fecha_factura' => $data['fecha_factura'],

            'total_factura' => 0,

            'total_productos_participantes' => 0,

            'porcentaje_cashback' => $campaign->porcentaje,

            'cashback_generado' => 0,

            'foto_factura' =>
            $data['foto_factura'] ?? null,

            'ocr_result' =>
            $data['ocr_result'] ?? null,

            'origen' =>
            $data['origen'] ?? 'manual',

            'estado' => 'procesando',
        ]);
    }

    /**
     * Crear productos de la factura.
     */
    private function createItems(
        Invoice $invoice,
        array $items
    ): array {

        $totalFactura = 0;

        foreach ($items as $item) {

            InvoiceItem::create([
                'invoice_id' => $invoice->id,

                'product_id' =>
                $item['product_id'],

                'valor' =>
                $item['valor'],
            ]);

            $totalFactura +=
                $item['valor'];
        }

        return [
            'total_factura' =>
            $totalFactura,

            'total_productos_participantes' =>
            $totalFactura,
        ];
    }

    /**
     * Actualizar totales de la factura.
     */
    private function updateInvoiceTotals(
        Invoice $invoice,
        array $totales
    ): void {

        $invoice->update([
            'total_factura' =>
            $totales['total_factura'],

            'total_productos_participantes' =>
            $totales['total_productos_participantes'],
        ]);
    }
}
