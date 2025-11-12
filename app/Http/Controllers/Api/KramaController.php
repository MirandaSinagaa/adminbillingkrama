<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Krama;
use App\Models\Tagihan; // <-- (BARU) Import Tagihan
use Illuminate\Http\Request;
use App\Http\Resources\KramaResource;
use App\Http\Resources\TagihanResource; // <-- (BARU) Import TagihanResource
use Illuminate\Support\Facades\Validator;

class KramaController extends Controller
{
    /**
     * Menampilkan semua data krama (dengan paginasi).
     * Ini untuk halaman "Data Warga".
     */
    public function index()
    {
        $kramas = Krama::with('banjar')->latest()->paginate(10);
        return KramaResource::collection($kramas);
    }

    /**
     * (METHOD BARU)
     * Mengambil semua krama (non-paginasi) untuk dropdown.
     */
    public function getKramaList()
    {
        $kramas = Krama::orderBy('name', 'asc')
                        ->get(['krama_id', 'name', 'nik']);
        return response()->json($kramas);
    }

    /**
     * Menyimpan krama baru (dari form "Tambah Warga").
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|unique:kramas',
            'name' => 'required|string|max:150',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $krama = Krama::create($validator->validated());
        return new KramaResource($krama->load('banjar'));
    }

    /**
     * Menampilkan satu data krama spesifik.
     */
    public function show(Krama $krama)
    {
        return new KramaResource($krama->load('banjar'));
    }

    /**
     * FUNGSI LAMA (Boleh disimpan jika masih perlu)
     */
    public function searchByNik($nik)
    {
        $krama = Krama::where('nik', $nik)->with('banjar')->first();
        if (!$krama) {
            return response()->json(['message' => 'Krama tidak ditemukan'], 404);
        }
        return new KramaResource($krama);
    }

    /**
     * Update data krama (untuk tombol "Edit").
     */
    public function update(Request $request, Krama $krama)
    {
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|unique:kramas,nik,' . $krama->krama_id . ',krama_id',
            'name' => 'required|string|max:150',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $krama->update($validator->validated());
        return new KramaResource($krama->load('banjar'));
    }

    /**
     * Hapus data krama (untuk tombol "Hapus").
     */
    public function destroy(Krama $krama)
    {
        // (OPSIONAL) Cek dulu jika krama punya tagihan
        if ($krama->tagihans()->count() > 0) {
             return response()->json(['message' => 'Tidak bisa hapus. Krama ini memiliki data tagihan.'], 422);
        }

        $krama->delete();
        return response()->json(['message' => 'Krama berhasil dihapus']);
    }

    /**
     * ==========================================================
     * (METHOD BARU UNTUK IDE #3)
     * Mengambil detail krama + semua riwayat tagihannya.
     * ==========================================================
     */
    public function getHistory(Krama $krama)
    {
        // 1. Ambil semua tagihan untuk krama ini
        $history = Tagihan::where('krama_id', $krama->krama_id)
                        ->with('pembayaran') // Load status pembayarannya
                        ->latest('tanggal') // Urutkan terbaru di atas
                        ->get();

        // 2. Kembalikan data krama DAN riwayat tagihannya
        return response()->json([
            'krama' => new KramaResource($krama->load('banjar')),
            'history' => TagihanResource::collection($history)
        ]);
    }
}