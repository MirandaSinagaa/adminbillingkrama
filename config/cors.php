<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    */

    'paths' => ['api/*', 'sanctum/csrf-cookie', 'login', 'register', 'logout'], // Tambahkan path auth spesifik

    'allowed_methods' => ['*'],

    'allowed_origins' => ['*'], // Buka untuk semua dulu agar tidak pusing

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false, // <--- WAJIB FALSE JIKA PAKAI BINTANG (*)
];