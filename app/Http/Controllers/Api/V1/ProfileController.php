<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Http\Request;

class ProfileController extends Controller
{
    public function show(Request $request)
    {
        $user = $request->user()->load([
            'role',
            'warehouse',
            'zone',
            'branch',
        ]);

        return ApiResponse::success(
            new UserResource($user),
            'Perfil obtenido correctamente.'
        );
    }
}
