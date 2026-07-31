<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Services\CashbackModuleService;
use App\Services\InvoiceService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use App\Http\Resources\CashbackCampaignResource;
use App\Http\Resources\CashbackBalanceResource;
use App\Http\Resources\CashbackHistoryResource;
use App\Http\Resources\InvoiceResource;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use Exception;

class CashbackController extends Controller
{
    public function __construct(
        private readonly CashbackModuleService $cashbackModuleService,
        private readonly InvoiceService $invoiceService
    ) {}

    /**
     * Obtener la campaña vigente.
     */
    public function currentCampaign()
    {
        try {

            $campaign = $this->cashbackModuleService->currentCampaign();

            return ApiResponse::success(
                $campaign
                    ? new CashbackCampaignResource($campaign)
                    : null,
                'Campaña obtenida correctamente.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Obtener saldo del usuario.
     */
    public function balance(Request $request)
    {
        try {

            $balance = $this->cashbackModuleService->balance(
                $request->user()
            );

            return ApiResponse::success(
                new CashbackBalanceResource($balance),
                'Saldo obtenido correctamente.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Historial del usuario.
     */
    public function history(Request $request)
    {
        try {

            $history = $this->cashbackModuleService->history(
                $request->user()
            );

            return ApiResponse::success(
                CashbackHistoryResource::collection($history),
                'Historial obtenido correctamente.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Detalle de una factura.
     */
    public function showInvoice(Request $request, int $invoice)
    {
        try {

            $invoice = $this->cashbackModuleService->showInvoice(
                $request->user(),
                $invoice
            );

            return ApiResponse::success(
                new InvoiceResource($invoice),
                'Factura obtenida correctamente.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }

    /**
     * Registrar factura.
     */
    public function storeInvoice(StoreInvoiceRequest $request)
    {
        try {

            $data = $request->validated();

            /*
            |--------------------------------------------------------------------------
            | Datos obtenidos del usuario autenticado
            |--------------------------------------------------------------------------
            */

            $data['user_id'] = $request->user()->id;
            $data['branch_id'] = $request->user()->branch_id;
            $data['origen'] = 'app';

            $invoice = $this->invoiceService->store($data);

            return ApiResponse::success(
                new InvoiceResource($invoice),
                'Factura registrada correctamente.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }
}
