<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    // Izinkan CORS untuk rute API dan Sanctum
    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', 'logout'],

    'allowed_methods' => ['*'],

    // (PENTING) Masukkan alamat Vercel Anda secara spesifik
    // Jangan pakai '*' lagi agar lebih pasti diterima browser
    'allowed_origins' => [
        'https://billing-krama-frontend.vercel.app', // Alamat Frontend Anda
        'http://localhost:5173', // Untuk testing di laptop
    ],

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    // Ubah ke true agar cookie/token diizinkan lewat
    'supports_credentials' => true,
];