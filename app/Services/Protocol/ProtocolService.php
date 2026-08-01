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
            |--------------------------------------------------------------------------
            | Eliminar aplicaciones anteriores
            |--------------------------------------------------------------------------
            |
            | Las relaciones hijas se eliminan mediante cascadeOnDelete.
            |
            */

            $protocol->applications()->delete();

            /*
            |--------------------------------------------------------------------------
            | Recrear aplicaciones
            |--------------------------------------------------------------------------
            */

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

            /*
            |--------------------------------------------------------------------------
            | Crear aplicación
            |--------------------------------------------------------------------------
            */

            $application = $protocol->applications()->create([
                'application_number' =>
                $applicationData['application_number'],

                'application_type' =>
                $applicationData['application_type'] ?? null,

                'description' =>
                $applicationData['description'] ?? null,
            ]);

            /*
            |--------------------------------------------------------------------------
            | Productos EOFertil directos
            |--------------------------------------------------------------------------
            */

            $this->syncProducts(
                $application,
                $applicationData['products'] ?? []
            );

            /*
            |--------------------------------------------------------------------------
            | Ingredientes activos
            |--------------------------------------------------------------------------
            */

            $this->syncActiveIngredients(
                $application,
                $applicationData['active_ingredients'] ?? []
            );
        }
    }

    /**
     * Crear productos EOFertil directos de una aplicación.
     */
    private function syncProducts(
        ProtocolApplication $application,
        array $products
    ): void {

        foreach ($products as $productData) {

            $application->products()->create([
                'product_id' =>
                $productData['product_id'],

                'dose' =>
                $productData['dose'],

                'unit' =>
                trim($productData['unit']),

                'application_base' =>
                trim($productData['application_base']),
            ]);
        }
    }

    /**
     * Crear ingredientes activos de una aplicación.
     */
    private function syncActiveIngredients(
        ProtocolApplication $application,
        array $activeIngredients
    ): void {

        foreach ($activeIngredients as $activeIngredientData) {

            /*
            |--------------------------------------------------------------------------
            | Crear ingrediente activo dentro de la aplicación
            |--------------------------------------------------------------------------
            */

            $protocolActiveIngredient =
                $application->activeIngredients()->create([
                    'active_ingredient_id' =>
                    $activeIngredientData['active_ingredient_id'],
                ]);

            /*
            |--------------------------------------------------------------------------
            | Crear productos recomendados para ese ingrediente
            |--------------------------------------------------------------------------
            */

            foreach (
                $activeIngredientData['products'] ?? []
                as $productData
            ) {

                $protocolActiveIngredient->products()->create([
                    'product_id' =>
                    $productData['product_id'],

                    'dose' =>
                    $productData['dose'],

                    'unit' =>
                    trim($productData['unit']),

                    'application_base' =>
                    trim($productData['application_base']),
                ]);
            }
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
