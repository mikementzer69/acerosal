<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {

        // 🟢 ESTO ARREGLA EL DISEÑO DESCONFIGURADO EN NGROK
        // Permite que Laravel confíe en las cabeceras de ngrok para cargar HTTPS/CSS
        $middleware->trustProxies(at: '*');

        // Alias de middlewares
        $middleware->alias([
            'sesion' => \App\Http\Middleware\VerificarSesion::class,
            'auth.custom' => \App\Http\Middleware\AuthCustom::class,
            'bloquear.mes' => \App\Http\Middleware\BloquearMesCerrado::class,
            'admin' => \App\Http\Middleware\SoloAdmin::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
