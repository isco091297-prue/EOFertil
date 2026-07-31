<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CashbackCampaignResource extends JsonResource
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

            'nombre' => $this->nombre,

            'descripcion' => $this->descripcion,

            'porcentaje' => (float) $this->porcentaje,

            'valor_alerta_factura' => $this->valor_alerta_factura !== null
                ? (float) $this->valor_alerta_factura
                : null,

            'fecha_inicio' => optional($this->fecha_inicio)->toDateString(),

            'fecha_fin' => optional($this->fecha_fin)->toDateString(),

            'estado' => $this->estado,

            'activo' => (bool) $this->activo,
        ];
    }
}
