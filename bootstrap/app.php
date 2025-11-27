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
        // Kita matikan CSRF untuk API agar tidak error 419
        $middleware->validateCsrfTokens(except: [
            '*'
        ]);
        
        // JANGAN tambahkan HandleCors manual di sini lagi
        // Laravel 11 akan otomatis membaca file config/cors.php
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();