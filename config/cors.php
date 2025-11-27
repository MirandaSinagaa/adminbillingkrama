<?php

return [
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', 'logout'],

    'allowed_methods' => ['*'],

    // (PENTING) JANGAN PAKAI BINTANG '*'.
    // Masukkan URL Frontend Vercel Anda persis seperti di browser (tanpa garis miring di akhir)
    'allowed_origins' => [
        'https://billing-krama-frontend.vercel.app', 
        'http://localhost:5173', // Tetap izinkan localhost untuk dev
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // (PENTING) Ini wajib true agar token login bisa lewat
    'supports_credentials' => true,
];