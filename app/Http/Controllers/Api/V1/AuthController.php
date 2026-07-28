<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Api\V1\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;
use App\Http\Requests\Api\V1\RegisterRequest;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {}

    public function login(LoginRequest $request)
    {
        try {

            $result = $this->authService->login(
                $request->validated()
            );

            return ApiResponse::success(
                [
                    'token' => $result['token'],
                    'user' => new UserResource($result['user']),
                    'showWelcome' => $result['showWelcome'],
                ],
                'Inicio de sesión correcto.'
            );
        } catch (AuthenticationException $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                401
            );
        }
    }
    public function register(RegisterRequest $request)
    {
        try {

            $this->authService->register(
                $request->validated()
            );

            return ApiResponse::success(
                null,
                'Tu solicitud fue registrada correctamente. Un administrador revisará tu información antes de habilitar tu acceso.'
            );
        } catch (Exception $e) {

            return ApiResponse::error(
                $e->getMessage(),
                null,
                500
            );
        }
    }
    public function logout(Request $request)
    {
        $this->authService->logout(
            $request->user()
        );

        return ApiResponse::success(
            null,
            'Sesión cerrada correctamente.'
        );
    }
    public function completeWelcome(Request $request)
    {
        $user = $request->user();

        if ($user->welcome_completed_at === null) {
            $user->welcome_completed_at = now();
            $user->save();
        }

        return ApiResponse::success(
            null,
            'Bienvenida completada correctamente.'
        );
    }
}
