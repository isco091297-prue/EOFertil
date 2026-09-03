<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Support\ApiResponse;
use Illuminate\Support\Facades\Storage;
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
    public function updatePhoto(Request $request)
    {
        $request->validate([
            'photo' => [
                'required',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:5120',
            ],
        ]);

        $user = $request->user();

        // Eliminar foto anterior si existe
        if ($user->photo) {
            Storage::disk('public')->delete($user->photo);
        }

        // Guardar nueva foto
        $path = $request->file('photo')->store(
            'profile_photos',
            'public'
        );

        $user->photo = $path;
        $user->save();

        return ApiResponse::success(
            new UserResource($user),
            'Foto de perfil actualizada correctamente.'
        );
    }
}
