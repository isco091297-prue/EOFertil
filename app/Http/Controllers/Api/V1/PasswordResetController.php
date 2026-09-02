<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\ForgotPasswordRequest;
use App\Http\Requests\ResetPasswordRequest;
use App\Models\PasswordReset;
use App\Models\User;
use App\Support\ApiResponse;
use Illuminate\Support\Str;

class PasswordResetController extends Controller
{
    /**
     * Verifica la identidad del usuario mediante:
     * - número de cédula
     * - últimos 4 dígitos del teléfono
     */
    public function verify(ForgotPasswordRequest $request)
    {
        $user = User::query()
            ->where('identification', $request->identification)
            ->first();

        if (! $user) {
            return ApiResponse::error(
                'Los datos ingresados no corresponden a una cuenta.',
                null,
                404
            );
        }

        $phone = preg_replace('/\D/', '', $user->phone ?? '');
        $phoneLast4 = substr($phone, -4);

        if (
            strlen($phoneLast4) !== 4 ||
            ! hash_equals($phoneLast4, $request->phone_last4)
        ) {
            return ApiResponse::error(
                'Los datos ingresados no corresponden a una cuenta.',
                null,
                404
            );
        }

        // Eliminar recuperaciones anteriores de este usuario.
        PasswordReset::where('user_id', $user->id)->delete();

        // Crear autorización temporal.
        $token = Str::random(64);

        PasswordReset::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => now()->addMinutes(15),
        ]);

        return ApiResponse::success(
            [
                'reset_token' => $token,
            ],
            'Identidad verificada correctamente.'
        );
    }

    /**
     * Cambia la contraseña utilizando una autorización temporal.
     */
    public function reset(ResetPasswordRequest $request)
    {
        $passwordReset = PasswordReset::query()
            ->where('token', $request->reset_token)
            ->first();

        if (! $passwordReset) {
            return ApiResponse::error(
                'La autorización de recuperación no es válida.',
                null,
                401
            );
        }

        // Verificar que la autorización no haya expirado.
        if ($passwordReset->expires_at->isPast()) {
            $passwordReset->delete();

            return ApiResponse::error(
                'La autorización de recuperación ha expirado. Inicia nuevamente el proceso.',
                null,
                401
            );
        }

        $user = User::find($passwordReset->user_id);

        if (! $user) {
            $passwordReset->delete();

            return ApiResponse::error(
                'No se encontró el usuario.',
                null,
                404
            );
        }

        // Cambiar la contraseña.
        $user->update([
            'password' => $request->password,
        ]);

        // Eliminar inmediatamente la autorización para que no pueda reutilizarse.
        $passwordReset->delete();

        // Por seguridad, cerrar cualquier sesión/token existente.
        $user->tokens()->delete();

        return ApiResponse::success(
            null,
            'Contraseña actualizada correctamente.'
        );
    }
}
