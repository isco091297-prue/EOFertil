<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashbackHistoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,

            'tipo' => $this->tipo,

            'movimiento' => $this->movimiento,

            'valor' => (float) $this->valor,

            'saldo_despues' => (float) $this->saldo_despues,

            'descripcion' => $this->descripcion,

            'fecha' => optional($this->created_at)
                ->format('Y-m-d H:i:s'),

            'campania' => $this->cashbackCampaign
                ? [
                    'id' => $this->cashbackCampaign->id,
                    'nombre' => $this->cashbackCampaign->nombre,
                ]
                : null,

            'factura' => $this->invoice
                ? [
                    'id' => $this->invoice->id,
                    'numero' => $this->invoice->numero_factura_original,
                    'estado' => $this->invoice->estado,
                ]
                : null,
        ];
    }
}
