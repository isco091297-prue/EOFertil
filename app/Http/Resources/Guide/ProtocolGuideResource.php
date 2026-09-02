<?php

namespace App\Http\Resources\Guide;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolGuideResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [

            'id' => $this->id,

            'codigo' => $this->code,

            'cultivo' => [
                'id' => $this->crop?->id,
                'nombre' => $this->crop?->name,
            ],

            'problema' => [
                'id' => $this->problem?->id,
                'nombre' => $this->problem?->name,
            ],

            'aplicaciones' => $this->whenLoaded('applications', function () {

                return $this->applications->map(function ($application) {

                    return [

                        'id' => $application->id,

                        'numero' => $application->application_number,

                        'tipo' => $application->application_type,

                        'descripcion' => $application->description,

                        /*
                        |--------------------------------------------------------------------------
                        | Productos EOFertil
                        |--------------------------------------------------------------------------
                        */

                        'productos' => $application->products
                            ->map(function ($item) {

                                return [

                                    'id' => $item->id,

                                    'dosis' => $item->dose,

                                    'unidad' => $item->unit,

                                    'base_aplicacion' => $item->application_base,

                                    'producto' => [

                                        'id' => $item->product?->id,

                                        'codigo' => $item->product?->code,

                                        'nombre' => $item->product?->name,

                                        'descripcion' =>
                                        $item->product?->description,

                                        'marca' =>
                                        $item->product?->brand?->name,

                                        'categoria' =>
                                        $item->product?->category?->name,

                                        'image_path' =>
                                        $item->product?->image_path,

                                        'image_url' =>
                                        $item->product?->image_url,

                                    ],

                                ];
                            })
                            ->values(),

                        /*
                        |--------------------------------------------------------------------------
                        | Ingredientes activos
                        |--------------------------------------------------------------------------
                        */

                        'ingredientes_activos' => $application
                            ->activeIngredients
                            ->map(function ($ingredient) {

                                return [

                                    'id' => $ingredient->id,

                                    'ingrediente_activo' => [

                                        'id' =>
                                        $ingredient
                                            ->activeIngredient?->id,

                                        'nombre' =>
                                        $ingredient
                                            ->activeIngredient?->name,

                                        'descripcion' =>
                                        $ingredient
                                            ->activeIngredient?->description,

                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Productos recomendados para el ingrediente
                                    |--------------------------------------------------------------------------
                                    */

                                    'productos' => $ingredient
                                        ->products
                                        ->map(function ($item) {

                                            return [

                                                'id' => $item->id,

                                                'dosis' => $item->dose,

                                                'unidad' => $item->unit,

                                                'base_aplicacion' =>
                                                $item->application_base,

                                                'producto' => [

                                                    'id' =>
                                                    $item->product?->id,

                                                    'codigo' =>
                                                    $item->product?->code,

                                                    'nombre' =>
                                                    $item->product?->name,

                                                    'descripcion' =>
                                                    $item->product?->description,

                                                    'marca' =>
                                                    $item->product?->brand?->name,

                                                    'categoria' =>
                                                    $item->product?->category?->name,

                                                    'image_path' =>
                                                    $item->product?->image_path,

                                                    'image_url' =>
                                                    $item->product?->image_url,

                                                ],

                                            ];
                                        })
                                        ->values(),

                                ];
                            })
                            ->values(),

                        /*
                        |--------------------------------------------------------------------------
                        | Combinaciones de ingredientes activos
                        |--------------------------------------------------------------------------
                        */

                        'combinaciones_ingredientes_activos' => $application
                            ->activeIngredientCombinations
                            ->map(function ($combination) {

                                return [

                                    'id' => $combination->id,

                                    'dosis' => $combination->dose,

                                    'unidad' => $combination->unit,

                                    'base_aplicacion' =>
                                    $combination->application_base,

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Información de la combinación
                                    |--------------------------------------------------------------------------
                                    */

                                    'combinacion' => [

                                        'id' =>
                                        $combination
                                            ->activeIngredientCombination?->id,

                                        'nombre' =>
                                        $combination
                                            ->activeIngredientCombination?->name,

                                        'descripcion' =>
                                        $combination
                                            ->activeIngredientCombination?->description,

                                    ],

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Ingredientes activos que forman la combinación
                                    |--------------------------------------------------------------------------
                                    */

                                    'ingredientes_activos' =>
                                    $combination
                                        ->activeIngredientCombination
                                        ?->activeIngredients
                                        ?->map(function ($ingredient) {

                                            return [

                                                'id' => $ingredient->id,

                                                'nombre' => $ingredient->name,

                                                'descripcion' =>
                                                $ingredient->description,

                                            ];
                                        })
                                        ?->values()
                                        ?? collect(),

                                    /*
                                    |--------------------------------------------------------------------------
                                    | Productos asociados a la combinación
                                    |--------------------------------------------------------------------------
                                    |
                                    | IMPORTANTE:
                                    | Flutter espera:
                                    |
                                    | {
                                    |   id,
                                    |   dosis,
                                    |   unidad,
                                    |   base_aplicacion,
                                    |   producto: {...}
                                    | }
                                    |
                                    */

                                    'productos' => $combination
                                        ->products
                                        ->map(function ($item) {

                                            return [

                                                'id' => $item->id,

                                                'dosis' => $item->dose,

                                                'unidad' => $item->unit,

                                                'base_aplicacion' =>
                                                $item->application_base,

                                                'producto' => [

                                                    'id' =>
                                                    $item->product?->id,

                                                    'codigo' =>
                                                    $item->product?->code,

                                                    'nombre' =>
                                                    $item->product?->name,

                                                    'descripcion' =>
                                                    $item->product?->description,

                                                    'marca' =>
                                                    $item->product?->brand?->name,

                                                    'categoria' =>
                                                    $item->product?->category?->name,

                                                    'image_path' =>
                                                    $item->product?->image_path,

                                                    'image_url' =>
                                                    $item->product?->image_url,

                                                ],

                                            ];
                                        })
                                        ->values(),

                                ];
                            })
                            ->values(),

                    ];
                })->values();
            }),

        ];
    }
}
