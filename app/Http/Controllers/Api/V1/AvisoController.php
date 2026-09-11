<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\AvisoResource;
use App\Models\Aviso;
use App\Support\ApiResponse;
use Exception;
use Illuminate\Http\Request;

class AvisoController extends Controller
{
    public function index(Request $request)
    {
        try {
            $avisos = Aviso::query()
                ->where('activo', true)
                ->orderByDesc('created_at')
                ->get();

            return ApiResponse::success(
                AvisoResource::collection($avisos),
                'Avisos obtenidos correctamente.'
            );
        } catch (Exception $e) {
            return ApiResponse::error(
                'No fue posible obtener los avisos.',
                null,
                500
            );
        }
    }
}