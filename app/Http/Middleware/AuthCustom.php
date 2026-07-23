<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Auth; // 👈 Agrega esta línea

class AuthCustom
{
    public function handle(Request $request, Closure $next)
    {
        if (!Session::has('idUsuario')) {
            return redirect('/login');
        }
        if (!Auth::check()) {
            Auth::loginUsingId(Session::get('idUsuario'));
        }

        return $next($request);
    }
}
