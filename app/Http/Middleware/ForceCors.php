<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Tangani request
        $response = $next($request);

        // 2. Tentukan Origin (Siapa yang meminta?)
        $origin = $request->header('Origin');

        // 3. Daftar Domain yang Diizinkan
        $allowedOrigins = [
            'https://billing-krama-frontend.vercel.app', // Frontend Vercel Anda
            'http://localhost:5173', // Localhost Laptop
        ];

        // 4. Tempelkan Header Izin
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        } else {
            // Opsional: Izinkan semua jika origin tidak dikenal (untuk debug)
            // $response->headers->set('Access-Control-Allow-Origin', '*'); 
        }

        $response->headers->set('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS');
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With, X-XSRF-TOKEN');
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        // 5. KHUSUS OPTIONS (Preflight): Langsung hentikan & kirim OK
        if ($request->isMethod('OPTIONS')) {
            $response->setStatusCode(200);
            $response->setContent(null);
        }

        return $response;
    }
}