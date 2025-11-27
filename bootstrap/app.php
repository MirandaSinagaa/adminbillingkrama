<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Pastikan HandleCors ada
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // 2. Matikan CSRF untuk API (agar tidak error 419/token mismatch)
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'login',
            'register',
            'logout'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();