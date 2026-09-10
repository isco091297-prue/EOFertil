<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InvoiceAuditResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'accion' => $this->accion,
            'motivo' => $this->motivo,
            'estado_anterior' => $this->estado_anterior,
            'estado_nuevo' => $this->estado_nuevo,
            'datos_anteriores' => $this->datos_anteriores,
            'datos_nuevos' => $this->datos_nuevos,
            'admin' => $this->whenLoaded('admin', function () {
                return [
                    'id' => $this->admin?->id,
                    'nombre' => trim(($this->admin?->first_name ?? '') . ' ' . ($this->admin?->last_name ?? '')),
                ];
            }),
            'fecha' => optional($this->created_at)->format('Y-m-d H:i:s'),
        ];
    }
}
