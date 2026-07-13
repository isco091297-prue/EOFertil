<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class LoginController extends Controller
{
    public function index(): View
    {
        return view('auth.login');
    }

    public function login(LoginRequest $request): RedirectResponse
    {
        $user = User::with('role')
            ->where('username', $request->username)
            ->first();

        if (!$user) {
            return back()
                ->withInput()
                ->withErrors([
                    'username' => 'Usuario o contraseña incorrectos.'
                ]);
        }

        if (!$user->is_active) {
            return back()->withErrors([
                'username' => 'La cuenta está deshabilitada.'
            ]);
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors([
                'username' => 'Usuario o contraseña incorrectos.'
            ]);
        }

        Auth::login($user);

        $request->session()->regenerate();
        $user->update([
            'last_login' => now(),
        ]);

        return redirect()->route('dashboard');
    }

    public function logout(): RedirectResponse
    {
        Auth::logout();

        request()->session()->invalidate();

        request()->session()->regenerateToken();

        return redirect()->route('login');
    }
}
