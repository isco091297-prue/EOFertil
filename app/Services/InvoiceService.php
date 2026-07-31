<?php

namespace App\Services;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;
use App\Models\Branch;
use App\Models\CashbackCampaign;
use App\Models\Product;
use App\Models\User;
use App\Models\InvoiceItem;
use Exception;

class InvoiceService
{
    public function __construct(
        protected CashbackService $cashbackService
    ) {}

    /**
     * Registrar una factura.
     *
     * @throws Exception
     */
    public function store(array $data): Invoice
    {
        return DB::transaction(function () use ($data) {

            /*
            |--------------------------------------------------------------------------
            | Validar información
            |--------------------------------------------------------------------------
            */

            $this->validateData($data);

            /*
            |--------------------------------------------------------------------------
            | Crear factura
            |--------------------------------------------------------------------------
            */

            $invoice = $this->createInvoice($data);

            /*
            |--------------------------------------------------------------------------
            | Crear productos
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

            $invoice->refresh();

            /*
|--------------------------------------------------------------------------
| Generar cashback
|--------------------------------------------------------------------------
*/

            $this->cashbackService->generate($invoice);

            /*
            |--------------------------------------------------------------------------
            | Retornar factura actualizada
            |--------------------------------------------------------------------------
            */

            return $invoice->fresh([
                'cashbackCampaign:id,nombre',
                'branch:id,name',
                'items.product:id,name',
            ]);
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
| Validar información principal
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

        if (
            !isset($data['branch_id']) ||
            !is_numeric($data['branch_id'])
        ) {
            throw new Exception(
                'No fue posible identificar la sucursal del usuario.'
            );
        }

        if (empty($data['cashback_campaign_id'])) {
            throw new Exception(
                'Debe indicar la campaña de cashback.'
            );
        }
        /*
    |--------------------------------------------------------------------------
    | Validar productos
    |--------------------------------------------------------------------------
    */

        $productos = [];

        foreach ($data['items'] as $index => $item) {
            if (empty($item['product_id'])) {
                throw new Exception(
                    'El producto de la fila ' . ($index + 1) . ' es obligatorio.'
                );
            }

            if (!isset($item['valor'])) {
                throw new Exception(
                    'Debe ingresar el valor del producto en la fila ' . ($index + 1) . '.'
                );
            }

            if (!is_numeric($item['valor'])) {
                throw new Exception(
                    'El valor del producto en la fila ' . ($index + 1) . ' es inválido.'
                );
            }

            if ($item['valor'] <= 0) {
                throw new Exception(
                    'El valor del producto en la fila ' . ($index + 1) . ' debe ser mayor que cero.'
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
| Validar reglas de negocio
|--------------------------------------------------------------------------
*/

        /*
|--------------------------------------------------------------------------
| Usuario
|--------------------------------------------------------------------------
*/

        $user = User::find($data['user_id']);

        if (!$user) {
            throw new Exception('El usuario no existe.');
        }

        if (!$user->is_active) {
            throw new Exception('El usuario se encuentra inactivo.');
        }

        if ($user->branch_id !== $data['branch_id']) {
            throw new Exception(
                'El usuario no pertenece a la sucursal seleccionada.'
            );
        }

        /*
|--------------------------------------------------------------------------
| Sucursal
|--------------------------------------------------------------------------
*/

        $branch = Branch::find($data['branch_id']);

        if (!$branch) {
            throw new Exception('La sucursal no existe.');
        }

        /*
|--------------------------------------------------------------------------
| Campaña
|--------------------------------------------------------------------------
*/

        $campaign = CashbackCampaign::find($data['cashback_campaign_id']);

        if (!$campaign) {
            throw new Exception('La campaña de cashback no existe.');
        }

        if (!$campaign->activo) {
            throw new Exception('La campaña de cashback está inactiva.');
        }

        if ($campaign->porcentaje <= 0) {
            throw new Exception(
                'La campaña de cashback no tiene un porcentaje válido.'
            );
        }

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
| Factura duplicada
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
| Productos
|--------------------------------------------------------------------------
*/

        $products = Product::whereIn(
            'id',
            array_column($data['items'], 'product_id')
        )->get()->keyBy('id');

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

            'numero_factura_original' => $data['numero_factura_original'],

            'fecha_factura' => $data['fecha_factura'],

            'total_factura' => 0,
            'total_productos_participantes' => 0,

            'porcentaje_cashback' => $campaign->porcentaje,
            'cashback_generado' => 0,

            'foto_factura' => $data['foto_factura'] ?? null,
            'ocr_result' => $data['ocr_result'] ?? null,

            'origen' => $data['origen'] ?? 'manual',

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
                'product_id' => $item['product_id'],
                'valor' => $item['valor'],
            ]);

            $totalFactura += $item['valor'];
        }

        return [
            'total_factura' => $totalFactura,
            'total_productos_participantes' => $totalFactura,
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
            'total_factura' => $totales['total_factura'],
            'total_productos_participantes' => $totales['total_productos_participantes'],
        ]);
    }
}
