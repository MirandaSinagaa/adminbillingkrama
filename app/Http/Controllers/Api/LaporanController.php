<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran; // <-- Laporan diambil dari data Pembayaran
use Illuminate\Http\Request;
use App\Http\Resources\PembayaranResource; // <-- Kita pakai resource lagi

class LaporanController extends Controller
{
    /**
     * ==========================================================
     * LOGIKA INTI "LAPORAN" (DARI WHITEBOARD ANDA)
     * ==========================================================
     * Mengambil laporan pembayaran berdasarkan bulan dan tahun.
     * * Akan diakses via: GET /api/laporan?bulan=10&tahun=2025
     */
    public function getLaporanBulanan(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'bulan' => 'required|integer|between:1,12',
            'tahun' => 'required|integer|digits:4',
        ]);

        $bulan = $validated['bulan'];
        $tahun = $validated['tahun'];

        // 2. Ambil data dari database
        $laporanPembayaran = Pembayaran::whereYear('tgl_bayar', $tahun)
                            ->whereMonth('tgl_bayar', $bulan)
                            ->where('status', 'selesai') // Hanya ambil yang lunas
                            ->with('tagihan.krama.banjar', 'adminPencatat') // Load semua relasi
                            ->get();

        // 3. Hitung total pemasukan
        $totalPemasukan = $laporanPembayaran->sum('jumlah');

        // 4. Kembalikan data dalam format JSON yang rapi
        return response()->json([
            'meta_laporan' => [
                'bulan' => $bulan,
                'tahun' => $tahun,
                'total_pemasukan' => (float) $totalPemasukan,
                'jumlah_transaksi' => $laporanPembayaran->count(),
            ],
            // Format rincian pembayaran menggunakan Resource
            'rincian_pembayaran' => PembayaranResource::collection($laporanPembayaran),
        ]);
    }
}
