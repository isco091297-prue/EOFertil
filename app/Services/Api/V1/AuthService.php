<?php

namespace App\Services\Api\V1;

use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Hash;

class AuthService
{
    public function login(array $credentials): array
    {
        $user = User::query()
            ->with([
                'role',
                'warehouse',
                'zone',
                'branch',
            ])
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
                'Tu cuenta está pendiente de aprobación por un administrador.'
            );
        }

        // Actualizar último acceso
        $user->update([
            'last_login' => now(),
        ]);

        // Refrescar modelo
        $user->refresh();

        // Un solo token activo por usuario
        $user->tokens()->delete();

        $token = $user
            ->createToken('flutter-app')
            ->plainTextToken;

        return [
            'user' => $user,
            'token' => $token,
        ];
    }

    public function logout(User $user): void
    {
        $user->currentAccessToken()?->delete();
    }

    public function register(array $data): User
    {
        $role = Role::query()
            ->where('name', 'Perchero')
            ->where('is_active', true)
            ->first();

        if (! $role) {
            throw new \Exception(
                'No existe un rol activo llamado Perchero.'
            );
        }

        return User::create([
            'role_id' => $role->id,

            'warehouse_id' => $data['warehouse_id'],

            'zone_id' => $data['zone_id'],

            'branch_id' => $data['branch_id'],

            'first_name' => $data['first_name'],

            'last_name' => $data['last_name'],

            'identification' => $data['identification'],

            'phone' => $data['phone'],

            // La cédula será el usuario
            'username' => $data['identification'],

            'email' => $data['email'] ?? null,

            'password' => $data['password'],

            'bank' => $data['bank'],

            'account_type' => $data['account_type'],

            'account_number' => $data['account_number'],

            // El administrador deberá aprobarlo
            'is_active' => false,

            'privacy_accepted' => true,

            'privacy_accepted_at' => now(),
        ]);
    }
}
