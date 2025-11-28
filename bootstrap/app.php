<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\ForceCors; // <-- Import Middleware Kita

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 1. Matikan CSRF untuk API
        $middleware->validateCsrfTokens(except: [
            '*'
        ]);

        // 2. Percayai Proxy (Wajib untuk Render HTTPS)
        $middleware->trustProxies(at: '*');

        // 3. PAKSA Middleware CORS kita jalan PALING PERTAMA
        $middleware->prepend(ForceCors::class); 
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();