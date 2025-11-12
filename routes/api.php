<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Import semua Controller yang sudah kita buat ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KramaController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\BanjarController;
use App\Http\Controllers\Api\DashboardController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Di sini Anda mendaftarkan rute API untuk aplikasi Anda. Rute-rute ini
| dimuat oleh RouteServiceProvider dan semuanya akan
| diberi prefix '/api'.
|
*/

// =========================================================================
// 1. ENDPOINT PUBLIK (Tidak perlu login)
// =========================================================================
// Sesuai modul (src 968), untuk register dan login admin
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);


// =========================================================================
// 2. ENDPOINT YANG DILINDUNGI (Wajib login sebagai admin)
// =========================================================================
// Sesuai modul (src 967), semua endpoint di dalam grup ini
// wajib menggunakan 'Bearer Token' dari Sanctum.
Route::middleware('auth:sanctum')->group(function () {
    
    // Endpoint Auth (Sesuai modul src 949 & 958)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

// --- (BARU) Rute untuk Statistik Dashboard ---
    Route::get('/dashboard-stats', [DashboardController::class, 'getStats']);
// --- (BARU) Rute untuk Chart ---
    Route::get('/dashboard-chart', [DashboardController::class, 'getChartData']);

    // Endpoint KRAMA
    // Endpoint khusus untuk "Cari NIK" (dari whiteboard Anda)
    // Harus di atas apiResource agar tidak bentrok
    Route::get('krama/search/{nik}', [KramaController::class, 'searchByNik']);
    // Endpoint CRUD standar untuk Krama
    Route::apiResource('krama', KramaController::class);
    
// --- (RUTE BARU UNTUK IDE #3) ---
    Route::get('/krama/{krama}/history', [KramaController::class, 'getHistory']);

    // Endpoint TAGIHAN
    // Endpoint CRUD standar untuk Tagihan
    // (Fungsi 'store' akan menjalankan logika "Iuran Read Only")
    Route::apiResource('tagihan', TagihanController::class);

    // Endpoint PEMBAYARAN
    // Endpoint CRUD standar untuk Pembayaran
    Route::apiResource('pembayaran', PembayaranController::class);

    // Endpoint LAPORAN
    // Endpoint khusus untuk "Laporan Bulanan" (dari whiteboard Anda)
    // * Akan diakses via: GET /api/laporan?bulan=10&tahun=2025
    Route::get('laporan', [LaporanController::class, 'getLaporanBulanan']);

// --- (RUTE BARU UNTUK FITUR INI) ---

    /**
     * Rute BARU: Mengambil daftar semua Krama (nama, nik) untuk dropdown
     * di halaman "Buat Tagihan".
     */
    Route::get('/krama-list', [KramaController::class, 'getKramaList']);

    /**
     * Rute BARU: Mengambil daftar semua Banjar untuk dropdown
     * di halaman "Tambah Warga".
     */
    Route::get('/banjar-list', [BanjarController::class, 'index']);
});
