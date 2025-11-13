<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Krama;
use App\Models\Tagihan;
use App\Models\Pembayaran;
use App\Models\Transaction;

class UserDashboardController extends Controller
{
    /**
     * Mengambil statistik untuk dashboard user.
     */
    public function getStats(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil Krama yang terhubung (wajib)
        $krama = Krama::where('user_id', $user->id)->first();
        if (!$krama) {
            return response()->json(['message' => 'Data Krama tidak ditemukan.'], 404);
        }
        $krama_id = $krama->krama_id;

        // 2. Hitung tagihan yang belum lunas
        // Ambil semua tagihan mentah yang belum punya 'pembayaran'
        $tagihansBelumLunas = Tagihan::where('krama_id', $krama_id)
                                ->whereDoesntHave('pembayaran') //
                                ->get();

        // 3. Hitung total tagihan belum lunas
        $total_tagihan_belum_lunas = $tagihansBelumLunas->sum(function($tagihan) {
            return $tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan;
        });

        // 4. Hitung jumlah tagihan belum lunas
        $jumlah_tagihan_belum_lunas = $tagihansBelumLunas->count();

        // 5. Hitung total pembayaran lunas (Semua riwayat)
        // (Ini menghitung dari tabel 'pembayarans' yang dibuat oleh Admin/Sistem)
        $total_pembayaran_lunas = Pembayaran::whereHas('tagihan', function($q) use ($krama_id) {
                                    $q->where('krama_id', $krama_id);
                                })
                                ->where('status', 'selesai')
                                ->sum('jumlah');

        // 6. Hitung jumlah transaksi (checkout) sukses
        // (Ini menghitung dari tabel 'transactions' yang dibuat oleh User)
        $jumlah_transaksi_sukses = Transaction::where('user_id', $user->id)
                                    ->where('status', 'paid')
                                    ->count();

        // 7. Kembalikan data
        return response()->json([
            'total_tagihan_belum_lunas' => (float) $total_tagihan_belum_lunas,
            'jumlah_tagihan_belum_lunas' => (int) $jumlah_tagihan_belum_lunas,
            'total_pembayaran_lunas' => (float) $total_pembayaran_lunas,
            'jumlah_transaksi_sukses' => (int) $jumlah_transaksi_sukses,
        ]);
    }
}