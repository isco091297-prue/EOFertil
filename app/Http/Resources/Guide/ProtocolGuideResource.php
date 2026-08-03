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

                        'productos' => $application->products->map(function ($item) {

                            return [

                                'id' => $item->id,

                                'dosis' => $item->dose,

                                'unidad' => $item->unit,

                                'base_aplicacion' => $item->application_base,

                                'producto' => [

                                    'id' => $item->product?->id,

                                    'codigo' => $item->product?->code,

                                    'nombre' => $item->product?->name,

                                    'marca' => $item->product?->brand?->name,

                                    'categoria' => $item->product?->category?->name,

                                    'image_path' => $item->product?->image_path,

                                    'image_url' => $item->product?->image_url,

                                ],

                            ];
                        })->values(),

                        /*
                        |--------------------------------------------------------------------------
                        | Ingredientes activos
                        |--------------------------------------------------------------------------
                        */

                        'ingredientes_activos' => $application->activeIngredients->map(function ($ingredient) {

                            return [

                                'id' => $ingredient->id,

                                'ingrediente_activo' => [

                                    'id' => $ingredient->activeIngredient?->id,

                                    'nombre' => $ingredient->activeIngredient?->name,

                                ],

                                'productos' => $ingredient->products->map(function ($item) {

                                    return [

                                        'id' => $item->id,

                                        'dosis' => $item->dose,

                                        'unidad' => $item->unit,

                                        'base_aplicacion' => $item->application_base,

                                        'producto' => [

                                            'id' => $item->product?->id,

                                            'codigo' => $item->product?->code,

                                            'nombre' => $item->product?->name,

                                            'marca' => $item->product?->brand?->name,

                                            'categoria' => $item->product?->category?->name,

                                            'image_path' => $item->product?->image_path,

                                            'image_url' => $item->product?->image_url,

                                        ],

                                    ];
                                })->values(),

                            ];
                        })->values(),

                    ];
                })->values();
            }),

        ];
    }
}
