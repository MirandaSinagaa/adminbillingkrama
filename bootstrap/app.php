<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Paksa Laravel mempercayai Load Balancer Render (Agar HTTPS terdeteksi)
        $middleware->trustProxies(at: '*');
        
        // 2. Pastikan HandleCors jalan paling awal
        $middleware->append(\Illuminate\Http\Middleware\HandleCors::class);

        // 3. Matikan CSRF untuk API
        $middleware->validateCsrfTokens(except: [
            '*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();