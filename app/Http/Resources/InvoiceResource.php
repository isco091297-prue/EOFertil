<?php

namespace App\Http\Resources;

use App\Models\CashbackTransaction;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        /*
        |--------------------------------------------------------------------------
        | Bono por primera factura
        |--------------------------------------------------------------------------
        |
        | Buscamos si esta factura generó la bonificación especial
        | correspondiente al logro de primera factura.
        |
        */

        $firstInvoiceBonus = CashbackTransaction::where(
            'invoice_id',
            $this->id
        )
            ->where('tipo', 'bonificacion')
            ->where(
                'descripcion',
                'Bono por registrar tu primera factura'
            )
            ->first();

        return [
            'id' => $this->id,

            'numero_factura' => $this->numero_factura_original,

            'fecha_factura' => optional($this->fecha_factura)
                ->toDateString(),

            'estado' => $this->estado,

            'total_factura' => (float) $this->total_factura,

            'total_productos_participantes' => (float) $this->total_productos_participantes,

            'porcentaje_cashback' => (float) $this->porcentaje_cashback,

            'cashback_generado' => (float) $this->cashback_generado,

            /*
            |--------------------------------------------------------------------------
            | Logro primera factura
            |--------------------------------------------------------------------------
            */

            'logro_primera_factura' => $firstInvoiceBonus !== null,

            'bono_primera_factura' => $firstInvoiceBonus
                ? (float) $firstInvoiceBonus->valor
                : 0.00,

            'foto_factura' => $this->foto_factura,

            'ocr_result' => $this->ocr_result,

            'campania' => $this->whenLoaded(
                'cashbackCampaign',
                fn() => [
                    'id' => $this->cashbackCampaign->id,
                    'nombre' => $this->cashbackCampaign->nombre,
                ]
            ),

            'sucursal' => $this->whenLoaded(
                'branch',
                fn() => [
                    'id' => $this->branch->id,
                    'nombre' => $this->branch->name,
                ]
            ),

            'productos' => InvoiceItemResource::collection(
                $this->whenLoaded('items')
            ),

            'created_at' => optional($this->created_at)
                ->format('Y-m-d H:i:s'),
        ];
    }
}
