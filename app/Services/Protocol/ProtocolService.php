<?php

namespace App\Services\Protocol;

use App\Models\Protocol;
use App\Models\ProtocolApplication;
use Illuminate\Support\Facades\DB;

class ProtocolService
{
    /**
     * Crear un protocolo completo.
     */
    public function store(array $data): Protocol
    {
        return DB::transaction(function () use ($data) {

            $protocol = Protocol::create([
                'code' => $this->generateCode(),
                'crop_id' => $data['crop_id'],
                'problem_id' => $data['problem_id'],
                'is_active' => true,
            ]);

            $this->syncApplications(
                $protocol,
                $data['applications']
            );

            return $protocol;
        });
    }

    /**
     * Actualizar un protocolo completo.
     */
    public function update(
        Protocol $protocol,
        array $data
    ): Protocol {
        return DB::transaction(function () use (
            $protocol,
            $data
        ) {

            $protocol->update([
                'crop_id' => $data['crop_id'],
                'problem_id' => $data['problem_id'],
            ]);

            /*
             |------------------------------------
             | Eliminamos todas las aplicaciones
             | y las recreamos.
             |------------------------------------
             */

            $protocol->applications()->delete();

            $this->syncApplications(
                $protocol,
                $data['applications']
            );

            return $protocol;
        });
    }

    /**
     * Eliminar un protocolo.
     */
    public function delete(Protocol $protocol): void
    {
        DB::transaction(function () use ($protocol) {
            $protocol->delete();
        });
    }

    /**
     * Crear todas las aplicaciones del protocolo.
     */
    private function syncApplications(
        Protocol $protocol,
        array $applications
    ): void {

        foreach ($applications as $applicationData) {

            $application = $protocol->applications()->create([
                'application_number' => $applicationData['application_number'],
                'application_type' => $applicationData['application_type'] ?? null,
                'description' => $applicationData['description'] ?? null,
            ]);

            $this->syncProducts(
                $application,
                $applicationData['products']
            );
        }
    }

    /**
     * Crear todos los productos de una aplicación.
     */
    private function syncProducts(
        ProtocolApplication $application,
        array $products
    ): void {

        foreach ($products as $productData) {

            $application->products()->create([
                'product_id' => $productData['product_id'],
                'dose' => $productData['dose'],
                'observations' => $productData['observations'] ?? null,
            ]);
        }
    }

    /**
     * Generar el código automático del protocolo.
     */
    private function generateCode(): string
    {
        $lastProtocol = Protocol::latest('id')->first();

        if ($lastProtocol === null) {
            return 'PRT0001';
        }

        $lastNumber = (int) substr(
            $lastProtocol->code,
            3
        );

        return 'PRT' . str_pad(
            (string) ($lastNumber + 1),
            4,
            '0',
            STR_PAD_LEFT
        );
    }
}
