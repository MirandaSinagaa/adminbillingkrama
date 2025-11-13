<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Krama;
use App\Models\Tagihan;
use App\Http\Resources\TagihanResource;
use Illuminate\Support\Facades\Auth;

class UserBillController extends Controller
{
    /**
     * FITUR 1: Mengambil tagihan BELUM LUNAS milik user yang login.
     * (PERBAIKAN LOGIKA PENGAMBILAN KRAMA)
     */
    public function getMyUnpaidBills(Request $request)
    {
        $user = Auth::user();
        
        // --- (PERBAIKAN) ---
        // $krama = $user->krama; // <-- Logika lama yang mungkin gagal karena cache
        
        // Logika Baru: Ambil krama secara manual berdasarkan user_id
        $krama = Krama::where('user_id', $user->id)->first();
        // --- (AKHIR PERBAIKAN) ---

        if (!$krama) {
            // (Jika ini masih gagal, berarti data link 100% tidak ada)
            return response()->json(['message' => 'Akun user ini tidak terhubung ke data Krama.'], 404);
        }

        // 2. Ambil tagihan yang BELUM BAYAR
        $tagihans = $this->getUnpaidBillsForKrama($krama->krama_id);

        return TagihanResource::collection($tagihans);
    }

    /**
     * FITUR 2 (Pencarian): Mencari Krama lain
     * (Tetap Sama)
     */
    public function searchKrama(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3']);
        $query = $request->input('q');

        $kramas = Krama::where(function($q) use ($query) {
                            $q->where('name', 'LIKE', "%{$query}%")
                              ->orWhere('nik', 'LIKE', "%{$query}%");
                        })
                        ->with('banjar')
                        ->take(10) 
                        ->get(['krama_id', 'nik', 'name', 'banjar_id']);
        
        return \App\Http\Resources\KramaResource::collection($kramas);
    }

    /**
     * FITUR 2 (Hasil): Mengambil tagihan BELUM LUNAS dari Krama lain
     * (Tetap Sama)
     */
    public function getKramaUnpaidBills(Krama $krama)
    {
        if (!$krama) {
            return response()->json(['message' => 'Krama tidak ditemukan.'], 404);
        }

        $tagihans = $this->getUnpaidBillsForKrama($krama->krama_id);

        return TagihanResource::collection($tagihans);
    }

    /**
     * Fungsi Helper (Internal) untuk mengambil tagihan
     * (PERBAIKAN LOGIKA: Memeriksa tagihan yang GAGAL/EXPIRED)
     */
    private function getUnpaidBillsForKrama($krama_id)
    {
        $tagihans = Tagihan::where('krama_id', $krama_id)
                            ->whereDoesntHave('pembayaran') // 1. Belum Lunas
                            ->where(function ($query) {
                                // 2. (Filter) Tagihan yang BELUM PERNAH masuk keranjang
                                $query->whereDoesntHave('transactionDetail')
                                      // 3. (Filter) ATAU tagihan yang GAGAL dibayar
                                      ->orWhereHas('transactionDetail.transaction', function ($subQuery) {
                                          $subQuery->whereIn('status', ['failed', 'expired']);
                                      });
                            })
                            ->with('krama.banjar')
                            ->latest('tanggal')
                            ->get();
        return $tagihans;
    }
}