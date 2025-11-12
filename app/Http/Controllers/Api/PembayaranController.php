<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan; // <-- Import Tagihan
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PembayaranResource;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon; // <-- (BARU) Import Carbon untuk tanggal

class PembayaranController extends Controller
{
    /**
     * Menampilkan semua data pembayaran.
     * (Method 'index' Anda tetap sama)
     */
    public function index()
    {
        $pembayarans = Pembayaran::with('tagihan.krama', 'adminPencatat')
                        ->latest()->paginate(10);
        return PembayaranResource::collection($pembayarans);
    }

    /**
     * ==========================================================
     * (PERUBAHAN BESAR)
     * Menyimpan data pembayaran baru.
     * Ini adalah logika untuk tombol "Validasi Lunas".
     * ==========================================================
     */
    public function store(Request $request)
    {
        // 1. Validasi HANYA tagihan_id
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihans,tagihan_id',
        ]);

        $tagihan = Tagihan::find($validated['tagihan_id']);

        // 2. Cek apakah tagihan ini sudah punya pembayaran
        if ($tagihan->pembayaran) {
            throw ValidationException::withMessages([
                'tagihan_id' => ['Tagihan ini sudah lunas atau dalam proses.'],
            ]);
        }

        // 3. (BARU) Server OTOMATIS menghitung total
        $totalTagihan = $tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan;

        // 4. Buat data pembayaran
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $validated['tagihan_id'],
            'tgl_bayar' => Carbon::now(), // <-- (BARU) Tanggal diisi otomatis
            'jumlah' => $totalTagihan, // <-- (BARU) Jumlah diisi otomatis
            'status' => 'selesai', // <-- (BARU) Status langsung 'selesai'
            'payment_by' => Auth::id(), // ID Admin yang sedang login
        ]);

        return new PembayaranResource($pembayaran->load('tagihan', 'adminPencatat'));
    }

    /**
     * Menampilkan satu data pembayaran.
     * (Method 'show' Anda tetap sama)
     */
    public function show(Pembayaran $pembayaran)
    {
        return new PembayaranResource(
            $pembayaran->load('tagihan.krama', 'adminPencatat')
        );
    }

    /**
     * Update data pembayaran.
     * (Method 'update' Anda tetap sama)
     */
    public function update(Request $request, Pembayaran $pembayaran)
    {
        $validated = $request->validate([
            'tgl_bayar' => 'sometimes|date',
            'status' => 'sometimes|in:pending,selesai',
        ]);

        $pembayaran->update($validated);
        
        return new PembayaranResource($pembayaran->load('tagihan', 'adminPencatat'));
    }

    /**
     * Hapus data pembayaran.
     * (Method 'destroy' Anda tetap sama)
     */
    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();
        return response()->json(['message' => 'Data pembayaran berhasil dihapus']);
    }
}