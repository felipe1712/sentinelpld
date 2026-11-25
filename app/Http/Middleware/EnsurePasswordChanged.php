<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsurePasswordChanged
{
    public function handle(Request $request, Closure $next)
    {
        $user = auth()->user();

        if ($user && $user->must_change_password && ! $request->routeIs('password.force.*') && ! $request->routeIs('logout')) {
            return redirect()->route('password.force.show')
                ->with('warning', 'Debes actualizar tu contraseña antes de continuar.');
        }

        return $next($request);
    }
}
