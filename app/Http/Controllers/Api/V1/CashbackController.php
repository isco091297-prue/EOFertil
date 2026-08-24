<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\StoreInvoiceRequest;
use App\Http\Resources\CashbackBalanceResource;
use App\Http\Resources\CashbackCampaignResource;
use App\Http\Resources\CashbackHistoryResource;
use App\Http\Resources\InvoiceResource;
use App\Services\Cashback\CashbackModuleService;
use App\Services\Invoice\InvoiceService;
use App\Services\Ranking\CampaignUserRankingService;
use App\Support\ApiResponse;
use Exception;
use RuntimeException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Http\Requests\Api\V1\StoreCashbackRedemptionRequest;
use App\Services\Cashback\CashbackRedemptionService;

class CashbackController extends Controller
{
    public function __construct(
        private readonly CashbackModuleService $cashbackModuleService,
        private readonly InvoiceService $invoiceService,
        private readonly CampaignUserRankingService $campaignUserRankingService,
        private readonly CashbackRedemptionService $cashbackRedemptionService
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
     * Solicitar canje de cashback.
     */
    public function redeem(
        StoreCashbackRedemptionRequest $request
    ) {
        try {

            $redemption = $this->cashbackRedemptionService->redeem(
                $request->user(),
                (float) $request->validated('monto')
            );

            return ApiResponse::success(
                [
                    'id' => $redemption->id,

                    'monto' =>
                    (float) $redemption->monto,

                    'estado' =>
                    $redemption->estado,

                    'cashback_available' =>
                    (float) $redemption->user->cashback_available,

                    'cashback_claimed' =>
                    (float) $redemption->user->cashback_claimed,

                    'telegram_enviado' =>
                    $redemption->telegram_enviado_at !== null,
                ],
                'Solicitud de canje registrada correctamente.'
            );
        } catch (RuntimeException $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                422
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                'No fue posible registrar la solicitud de canje.',
                null,
                500
            );
        }
    }

    /**
     * Ranking vigente para la aplicación móvil.
     */
    public function ranking(Request $request)
    {
        try {
            $ranking = $this->campaignUserRankingService
                ->getMobileRanking($request->user());

            return ApiResponse::success(
                $ranking,
                'Ranking obtenido correctamente.'
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

            $invoice = $this->cashbackModuleService->invoice(
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
        $photoPath = null;

        try {

            $data = $request->validated();

            /*
            |--------------------------------------------------------------------------
            | Datos obtenidos del usuario autenticado
            |--------------------------------------------------------------------------
            */

            $data['user_id'] = $request->user()->id;
            $data['branch_id'] = $request->user()->branch_id;
            $data['origen'] = 'manual';

            /*
            |--------------------------------------------------------------------------
            | Guardar fotografía de la factura
            |--------------------------------------------------------------------------
            */

            if ($request->hasFile('foto_factura')) {

                $photoPath = $request
                    ->file('foto_factura')
                    ->store('invoices', 'public');

                $data['foto_factura'] = $photoPath;
            }

            /*
            |--------------------------------------------------------------------------
            | Registrar factura
            |--------------------------------------------------------------------------
            */

            $invoice = $this->invoiceService->store($data);

            return ApiResponse::success(
                new InvoiceResource($invoice),
                'Factura registrada correctamente.'
            );
        } catch (Exception $e) {

            /*
            |--------------------------------------------------------------------------
            | Eliminar fotografía si falla el registro
            |--------------------------------------------------------------------------
            |
            | La fotografía se guarda antes de crear la factura.
            | Si posteriormente falla la transacción, eliminamos el archivo
            | para no dejar imágenes huérfanas en storage.
            |
            */

            if (
                $photoPath !== null &&
                Storage::disk('public')->exists($photoPath)
            ) {
                Storage::disk('public')->delete($photoPath);
            }

            return ApiResponse::error(
                $e->getMessage(),
                null,
                422
            );
        }
    }
}
