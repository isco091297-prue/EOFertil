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

                        'productos' => $application->products->map(function ($item) {

                            return [

                                'id' => $item->id,

                                'dosis' => $item->dose,

                                'observaciones' => $item->observations,

                                'producto' => [

                                    'id' => $item->product?->id,

                                    'nombre' => $item->product?->name,

                                    'marca' => $item->product?->brand?->name,

                                    'categoria' => $item->product?->category?->name,

                                ],

                            ];
                        })->values(),

                    ];
                })->values();
            }),
        ];
    }
}
