<?php

namespace App\Services\Cashback;

use App\Models\CashbackCampaign;
use Illuminate\Support\Facades\DB;

class CashbackCampaignService
{
    public function __construct(
        protected CashbackCampaignParticipantService $participantService
    ) {}

    /**
     * Crear campaña.
     */
    public function store(
        array $data
    ): CashbackCampaign {

        return DB::transaction(function () use ($data) {

            $campaign = CashbackCampaign::create([

                'nombre' => $data['nombre'],

                'descripcion' => $data['descripcion'] ?? null,

                'campaign_type' => $data['campaign_type'],

                'participant_type' => $data['participant_type'],

                'porcentaje' => $data['porcentaje'] ?? null,

                'valor_alerta_factura' =>
                $data['valor_alerta_factura'] ?? 0,

                'fecha_inicio' => $data['fecha_inicio'],

                'fecha_fin' => $data['fecha_fin'],

                'activo' => $data['activo'],

            ]);

            $this->participantService->save(
                $campaign,
                $data
            );

            return $campaign;
        });
    }

    /**
     * Actualizar campaña.
     */
    public function update(
        CashbackCampaign $campaign,
        array $data
    ): CashbackCampaign {

        return DB::transaction(function () use (
            $campaign,
            $data
        ) {

            $campaign->update([

                'nombre' => $data['nombre'],

                'descripcion' => $data['descripcion'] ?? null,

                'campaign_type' => $data['campaign_type'],

                'participant_type' => $data['participant_type'],

                'porcentaje' => $data['porcentaje'] ?? null,

                'valor_alerta_factura' =>
                $data['valor_alerta_factura'] ?? 0,

                'fecha_inicio' => $data['fecha_inicio'],

                'fecha_fin' => $data['fecha_fin'],

                'activo' => $data['activo'],

            ]);

            $this->participantService->save(
                $campaign,
                $data
            );

            return $campaign;
        });
    }

    /**
     * Eliminar campaña.
     */
    public function delete(
        CashbackCampaign $campaign
    ): void {

        DB::transaction(function () use (
            $campaign
        ) {

            $campaign->delete();
        });
    }
}
