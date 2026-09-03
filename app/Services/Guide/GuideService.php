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
            ->get()
            ->map(function ($problem) {
                return [
                    'id' => $problem->id,
                    'code' => $problem->code,
                    'name' => $problem->name,
                    'crop_id' => $problem->crop_id,
                    'image_path' => $problem->image_path,
                    'image_url' => $problem->image_path
                        ? url('storage/' . $problem->image_path)
                        : null,
                    'description' => $problem->description,
                    'is_active' => (bool) $problem->is_active,
                ];
            });
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

                /*
                |--------------------------------------------------------------------------
                | Cultivo y problema
                |--------------------------------------------------------------------------
                */

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
                | Productos EOFertil directos
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
                | Productos recomendados para ingredientes activos
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredients.products',
                'applications.activeIngredients.products.product',
                'applications.activeIngredients.products.product.brand',
                'applications.activeIngredients.products.product.category',

                /*
                |--------------------------------------------------------------------------
                | Combinaciones de ingredientes activos
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredientCombinations',

                /*
                |--------------------------------------------------------------------------
                | Información de la combinación
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredientCombinations.activeIngredientCombination',

                /*
                |--------------------------------------------------------------------------
                | Ingredientes que forman la combinación
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredientCombinations.activeIngredientCombination.activeIngredients',

                /*
                |--------------------------------------------------------------------------
                | Productos asociados a la combinación
                |--------------------------------------------------------------------------
                */

                'applications.activeIngredientCombinations.activeIngredientCombination.products',
                'applications.activeIngredientCombinations.activeIngredientCombination.products.brand',
                'applications.activeIngredientCombinations.activeIngredientCombination.products.category',
                'applications.activeIngredientCombinations.products.product',
                'applications.activeIngredientCombinations.products.product.brand',
                'applications.activeIngredientCombinations.products.product.category',

            ])
            ->where('crop_id', $cropId)
            ->where('problem_id', $problemId)
            ->where('is_active', true)
            ->first();
    }
}
