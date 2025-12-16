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

        // Alias de middlewares
        $middleware->alias([
            'auth.custom' => \App\Http\Middleware\AuthCustom::class,
            'bloquear.mes' => \App\Http\Middleware\BloquearMesCerrado::class, // ⬅️ NUEVO
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
