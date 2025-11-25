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
    // ... (Method getMyUnpaidBills, searchKrama, getKramaUnpaidBills TETAP SAMA) ...
    public function getMyUnpaidBills(Request $request)
    {
        $user = Auth::user();
        // Cari Krama manual (seperti perbaikan sebelumnya)
        $krama = Krama::where('user_id', $user->id)->first(); 
        
        if (!$krama) {
            return response()->json(['message' => 'Akun user ini tidak terhubung ke data Krama.'], 404);
        }

        $tagihans = $this->getUnpaidBillsForKrama($krama->krama_id);
        return TagihanResource::collection($tagihans);
    }

    public function searchKrama(Request $request)
    {
        $request->validate(['q' => 'required|string|min:3']);
        $query = $request->input('q');
        $kramas = Krama::where(function($q) use ($query) {
            $q->where('name', 'LIKE', "%{$query}%")->orWhere('nik', 'LIKE', "%{$query}%");
        })->with('banjar')->take(10)->get(['krama_id', 'nik', 'name', 'banjar_id']);
        return \App\Http\Resources\KramaResource::collection($kramas);
    }

    public function getKramaUnpaidBills(Krama $krama)
    {
        if (!$krama) { return response()->json(['message' => 'Krama tidak ditemukan.'], 404); }
        $tagihans = $this->getUnpaidBillsForKrama($krama->krama_id);
        return TagihanResource::collection($tagihans);
    }

    /**
     * Helper: Ambil tagihan, termasuk yang sedang pending di transaksi
     */
    private function getUnpaidBillsForKrama($krama_id)
    {
        $tagihans = Tagihan::where('krama_id', $krama_id)
            ->whereDoesntHave('pembayaran') // Pastikan belum lunas di tabel pembayaran
            ->with([
                'krama.banjar', 
                // Load relasi transaksi pending jika ada
                'transactionDetail.transaction' => function($q) {
                    $q->where('status', 'pending');
                }
            ])
            ->latest('tanggal')
            ->get();

        return $tagihans;
    }
}