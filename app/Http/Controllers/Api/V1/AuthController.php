<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\V1\LoginRequest;
use App\Http\Resources\UserResource;
use App\Services\Api\V1\AuthService;
use App\Support\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Auth\AuthenticationException;

class AuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService
    ) {
    }

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
}
