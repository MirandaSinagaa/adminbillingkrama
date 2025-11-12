<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Tagihan;
use App\Models\Krama;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\TagihanResource;
use Carbon\Carbon;

class TagihanController extends Controller
{
    /**
     * Menampilkan semua tagihan, dengan filter bulan & tahun.
     * (Method 'index' Anda tidak berubah)
     */
    public function index(Request $request)
    {
        $request->validate([
            'bulan' => 'sometimes|integer|between:1,12',
            'tahun' => 'sometimes|integer|digits:4',
        ]);

        $query = Tagihan::with('krama.banjar', 'adminPembuat', 'pembayaran');

        if ($request->has('bulan') && $request->has('tahun')) {
            $query->whereYear('tanggal', $request->tahun)
                  ->whereMonth('tanggal', $request->bulan);
        } else {
            $query->where('tanggal', '>=', Carbon::now()->subMonths(3));
        }

        $tagihans = $query->latest()->paginate(5);
        
        return TagihanResource::collection($tagihans);
    }

    /**
     * ==========================================================
     * (PERUBAHAN)
     * Menyimpan tagihan baru, IURAN sekarang diisi oleh admin.
     * ==========================================================
     */
    public function store(Request $request)
    {
        // 1. Validasi input dari Admin
        // (PERUBAHAN: 'iuran' sekarang wajib ada dari frontend)
        $validated = $request->validate([
            'krama_id' => 'required|exists:kramas,krama_id',
            'iuran' => 'required|numeric|min:0', // <-- (BARU) Iuran sekarang divalidasi
            'dedosan' => 'required|numeric|min:0',
            'peturuhan' => 'required|numeric|min:0',
            'tanggal' => 'required|date',
        ]);

        // 2. (DIHAPUS) Logika "Iuran Read Only" sudah tidak diperlukan
        //    Server sekarang percaya 100% pada 'iuran' yang dikirim admin.

        // 3. Buat tagihan baru
        $tagihan = Tagihan::create([
            'krama_id' => $validated['krama_id'],
            'iuran' => $validated['iuran'], // <-- (DIUBAH) Ambil dari validasi
            'dedosan' => $validated['dedosan'],
            'peturuhan' => $validated['peturuhan'],
            'tanggal' => $validated['tanggal'],
            'created_by' => Auth::id(),
        ]);

        // 4. Kembalikan data
        return new TagihanResource($tagihan->load('krama', 'adminPembuat'));
    }

    /**
     * Menampilkan satu tagihan spesifik.
     * (Method 'show' Anda tetap sama)
     */
    public function show(Tagihan $tagihan)
    {
        return new TagihanResource(
            $tagihan->load('krama.banjar', 'adminPembuat', 'pembayaran')
        );
    }

    /**
     * Update tagihan.
     * (Method 'update' Anda tetap sama)
     */
    public function update(Request $request, Tagihan $tagihan)
    {
        // (Kita juga bisa tambahkan 'iuran' di sini jika diperlukan)
        $validated = $request->validate([
            'iuran' => 'sometimes|numeric|min:0', // <-- (BARU) Iuran boleh diupdate
            'dedosan' => 'sometimes|numeric|min:0',
            'peturuhan' => 'sometimes|numeric|min:0',
            'tanggal' => 'sometimes|date',
        ]);

        $tagihan->update($validated);

        return new TagihanResource($tagihan->load('krama', 'adminPembuat'));
    }

    /**
     * Hapus tagihan.
     * (Method 'destroy' Anda tetap sama)
     */
    public function destroy(Tagihan $tagihan)
    {
        if ($tagihan->pembayaran) {
            return response()->json([
                'message' => 'Tidak bisa hapus. Tagihan ini sudah dibayar.'
            ], 403);
        }

        $tagihan->delete();
        return response()->json(['message' => 'Tagihan berhasil dihapus']);
    }
}