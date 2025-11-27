<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Cors
{
    public function handle(Request $request, Closure $next): Response
    {
        // 1. Tangani Preflight Request (OPTIONS) secara langsung
        // Browser tanya: "Boleh gak saya kirim data?"
        // Kita jawab langsung: "Boleh (200 OK)"
        if ($request->isMethod('OPTIONS')) {
            $response = response('', 200);
        } else {
            // Jika bukan OPTIONS, lanjutkan proses ke Controller
            $response = $next($request);
        }

        // 2. Tempelkan Header Izin (Surat Jalan)
        // Kita gunakan Origin dari request agar dinamis & aman
        $origin = $request->header('Origin');
        
        // Daftar domain yang diizinkan (Whitelist)
        $allowedOrigins = [
            'https://billing-krama-frontend.vercel.app', // Frontend Anda
            'http://localhost:5173', // Localhost Anda
        ];

        // Jika Origin ada di daftar putih, izinkan
        if (in_array($origin, $allowedOrigins)) {
            $response->headers->set('Access-Control-Allow-Origin', $origin);
        }

        // Izinkan Method apa saja
        $response->headers->set('Access-Control-Allow-Methods', 'POST, GET, OPTIONS, PUT, DELETE');
        
        // Izinkan Header apa saja
        $response->headers->set('Access-Control-Allow-Headers', 'Content-Type, Accept, Authorization, X-Requested-With, Application');
        
        // Izinkan Kredensial (Cookie/Token)
        $response->headers->set('Access-Control-Allow-Credentials', 'true');

        return $response;
    }
}