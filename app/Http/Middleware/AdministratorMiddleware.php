<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class AdministratorMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (!Auth::check()) {
            return redirect()->route('login');
        }

        if (!Auth::user()->role || Auth::user()->role->name !== 'Administrator') {
            abort(403, 'No tiene permisos para acceder al sistema.');
        }
        return $next($request);
    }
}
