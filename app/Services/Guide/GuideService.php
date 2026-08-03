<?php

namespace App\Services\Guide;

use App\Models\Crop;
use App\Models\Problem;
use App\Models\Protocol;

class GuideService
{
    /**
     * Obtener todos los cultivos activos.
     */
    public function crops()
    {
        return Crop::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener los problemas de un cultivo.
     */
    public function problems(int $cropId)
    {
        return Problem::query()
            ->where('crop_id', $cropId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * Obtener el protocolo completo.
     */
    public function protocol(
        int $cropId,
        int $problemId
    ): ?Protocol {

        return Protocol::query()

            ->with([

                'crop',

                'problem',

                /*
                |--------------------------------------------------------------------------
                | Aplicaciones
                |--------------------------------------------------------------------------
                */

                'applications',

                /*
                |--------------------------------------------------------------------------
                | Productos EOFertil
                |--------------------------------------------------------------------------
                */

                'applications.products',
                'applications.products.product',
                'applications.products.product.brand',
                'applications.products.product.category',

                /*
                |--------------------------------------------------------------------------
                | Ingredientes activos
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredients',
                'applications.activeIngredients.activeIngredient',

                /*
                |--------------------------------------------------------------------------
                | Productos recomendados
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredients.products',
                'applications.activeIngredients.products.product',
                'applications.activeIngredients.products.product.brand',
                'applications.activeIngredients.products.product.category',

            ])

            ->where('crop_id', $cropId)

            ->where('problem_id', $problemId)

            ->where('is_active', true)

            ->first();
    }
}
