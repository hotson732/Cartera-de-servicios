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
    ->withMiddleware(function (Middleware $middleware): void {
        //Agregacion del middleware para verificar el rol aqui es donde se pide el intermediario que verifique el proceso de autenticacion y autorizacion del usuario.
        $middleware->alias([
        'role' => \App\Http\Middleware\CheckRole::class
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //falta implementar
    })->create();
