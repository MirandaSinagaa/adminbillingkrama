<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use App\Http\Middleware\Cors; // <--- 1. Import Middleware Baru

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        // 2. Hapus HandleCors bawaan Laravel jika ada, ganti dengan punya kita
        // Letakkan di urutan paling awal agar dieksekusi duluan
        $middleware->append(Cors::class);

        // 3. Matikan CSRF untuk API (Wajib untuk API Token)
        $middleware->validateCsrfTokens(except: [
            '*'
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        //
    })->create();