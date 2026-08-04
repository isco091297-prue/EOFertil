<?php

namespace App\Services\Cashback;

use App\Models\Branch;
use App\Models\CashbackCampaign;
use App\Models\Warehouse;
use App\Models\Zone;

class CashbackCampaignParticipantService
{
    /**
     * Datos para la vista de participantes.
     */
    public function data(
        CashbackCampaign $campaign
    ): array {

        return [

            'warehouses' => Warehouse::query()
                ->orderBy('name')
                ->get(),

            'zones' => Zone::query()
                ->orderBy('name')
                ->get(),

            'branches' => Branch::query()
                ->orderBy('name')
                ->get(),

            'selectedWarehouses' => $campaign->scopes()
                ->where('scope_type', 'warehouse')
                ->pluck('scope_id')
                ->toArray(),

            'selectedZones' => $campaign->scopes()
                ->where('scope_type', 'zone')
                ->pluck('scope_id')
                ->toArray(),

            'selectedBranches' => $campaign->scopes()
                ->where('scope_type', 'branch')
                ->pluck('scope_id')
                ->toArray(),

        ];
    }

    /**
     * Guardar participantes.
     */
    public function save(
        CashbackCampaign $campaign,
        array $data
    ): void {

        $campaign->scopes()->delete();

        if (
            ($data['participant_type'] ?? 'all')
            === 'all'
        ) {
            return;
        }

        $this->sync(
            $campaign,
            'warehouse',
            $data['warehouse_ids'] ?? []
        );

        $this->sync(
            $campaign,
            'zone',
            $data['zone_ids'] ?? []
        );

        $this->sync(
            $campaign,
            'branch',
            $data['branch_ids'] ?? []
        );
    }

    /**
     * Sincronizar participantes.
     */
    private function sync(
        CashbackCampaign $campaign,
        string $scopeType,
        array $ids
    ): void {

        foreach ($ids as $id) {

            $campaign->scopes()->create([

                'scope_type' => $scopeType,

                'scope_id' => $id,

                'required' => true,

            ]);
        }
    }
}
