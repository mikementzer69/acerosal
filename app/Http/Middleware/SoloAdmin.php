<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SoloAdmin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next)
    {
        // Usamos la Facade para que el editor reconozca los métodos
        if (Auth::check() && Auth::user()->id_rol == 1) {
            return $next($request);
        }

        // Si no es admin, lo mandamos al dashboard con un aviso
        return redirect()->route('dashboard')->with('error', '🚫 Acceso denegado: Solo para administradores.');
    }
}
