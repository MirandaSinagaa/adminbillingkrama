<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// --- Import semua Controller ---
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\KramaController;
use App\Http\Controllers\Api\TagihanController;
use App\Http\Controllers\Api\PembayaranController;
use App\Http\Controllers\Api\LaporanController;
use App\Http\Controllers\Api\BanjarController;
use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\UserBillController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\PaymentConfirmationController;

// (PERUBAHAN) Import 3 Controller Baru
use App\Http\Controllers\Api\UserDashboardController;
use App\Http\Controllers\Api\UserTransactionController;
use App\Http\Controllers\Api\UserProfileController;


// =========================================================================
// 1. ENDPOINT PUBLIK (Tidak perlu login)
// =========================================================================
Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);
Route::get('/banjar-list-public', [BanjarController::class, 'index']);


// =========================================================================
// 2. ENDPOINT YANG DILINDUNGI (Wajib login)
// =========================================================================
Route::middleware('auth:sanctum')->group(function () {
    
    // Endpoint Auth (Global)
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ========================
    // --- Rute Admin Panel ---
    // ========================
    Route::prefix('admin')->group(function () {
        Route::get('/dashboard-stats', [DashboardController::class, 'getStats']);
        Route::get('/dashboard-chart', [DashboardController::class, 'getChartData']);
        Route::get('krama/search/{nik}', [KramaController::class, 'searchByNik']);
        Route::apiResource('krama', KramaController::class);
        Route::get('/krama/{krama}/history', [KramaController::class, 'getHistory']);
        Route::apiResource('tagihan', TagihanController::class);
        Route::apiResource('pembayaran', PembayaranController::class);
        Route::get('laporan', [LaporanController::class, 'getLaporanBulanan']);
        Route::get('/krama-list', [KramaController::class, 'getKramaList']);
        Route::get('/banjar-list', [BanjarController::class, 'index']);
    });
    
    // ========================
    // --- Rute User Panel ---
    // ========================
    Route::prefix('user')->group(function () {
        // Rute Pembayaran (Sudah ada)
        Route::get('/my-unpaid-bills', [UserBillController::class, 'getMyUnpaidBills']);
        Route::get('/search-krama', [UserBillController::class, 'searchKrama']);
        Route::get('/krama/{krama}/unpaid-bills', [UserBillController::class, 'getKramaUnpaidBills']);
        Route::post('/checkout', [CheckoutController::class, 'store']);
        Route::post('/confirm-payment', [PaymentConfirmationController::class, 'store']);
        
        // --- (RUTE BARU DARI RENCANA) ---

        // 1. Rute Dashboard Profesional
        Route::get('/dashboard-stats', [UserDashboardController::class, 'getStats']);
        
        // 2. Rute Riwayat Transaksi
        Route::get('/my-transactions', [UserTransactionController::class, 'index']);
        
        // 3. Rute Edit Profil
        Route::get('/my-profile', [UserProfileController::class, 'show']);
        Route::put('/my-profile', [UserProfileController::class, 'update']);
    });
});