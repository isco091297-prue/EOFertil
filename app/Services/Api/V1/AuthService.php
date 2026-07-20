<?php

namespace App\Services\Api\V1;

use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::query()
            ->where('username', $credentials['username'])
            ->orWhere('email', $credentials['username'])
            ->first();

        if (! $user) {
            throw new AuthenticationException(
                'Usuario o contraseña incorrectos.'
            );
        }

        if (! Hash::check($credentials['password'], $user->password)) {
            throw new AuthenticationException(
                'Usuario o contraseña incorrectos.'
            );
        }

        if (! $user->is_active) {
            throw new AuthenticationException(
                'El usuario se encuentra inactivo.'
            );
        }

        $user->update([
            'last_login' => now(),
        ]);

        $token = $user->createToken('flutter-app')->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

   public function logout(User $user): void
{
    $user->currentAccessToken()?->delete();
}
}
