<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Krama;
use App\Models\Tagihan;
use App\Models\User; 
use Illuminate\Http\Request;
use App\Http\Resources\KramaResource;
use App\Http\Resources\TagihanResource; 
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Hash; 

class KramaController extends Controller
{
    /**
     * Menampilkan semua data krama (dengan paginasi).
     * (Tetap Sama)
     */
    public function index()
    {
        $kramas = Krama::with('banjar', 'user')->latest()->paginate(10);
        return KramaResource::collection($kramas);
    }

    /**
     * Mengambil semua krama (non-paginasi) untuk dropdown.
     * (Tetap Sama)
     */
    public function getKramaList()
    {
        $kramas = Krama::orderBy('name', 'asc')
                        ->get(['krama_id', 'name', 'nik']);
        return response()->json($kramas);
    }

    /**
     * Menyimpan krama baru (Skenario 2: Admin mendaftarkan warga + akun).
     * (Tetap Sama)
     */
    public function store(Request $request)
    {
        // 1. Validasi (Termasuk Email & Password)
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|unique:kramas',
            'name' => 'required|string|max:150',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',
            // Validasi untuk akun login
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        // 2. Gunakan Transaction
        try {
            DB::beginTransaction();

            // 3. Buat Akun User (Login) terlebih dahulu
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                'role' => 'user', 
            ]);

            // 4. Buat Data Krama (Warga)
            $krama = Krama::create([
                'user_id' => $user->id, 
                'nik' => $validated['nik'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'status' => $validated['status'],
                'banjar_id' => $validated['banjar_id'],
            ]);

            DB::commit();

            return new KramaResource($krama->load('banjar', 'user'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal membuat warga baru, terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Menampilkan satu data krama spesifik.
     * (Tetap Sama)
     */
    public function show(Krama $krama)
    {
        return new KramaResource($krama->load('banjar', 'user'));
    }

    /**
     * Update data krama (termasuk update data akun login).
     * (Tetap Sama)
     */
    public function update(Request $request, Krama $krama)
    {
        $user = $krama->user; 
        if (!$user) {
            return response()->json(['message' => 'Data Krama tidak terhubung dengan Akun User.'], 404);
        }

        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|unique:kramas,nik,' . $krama->krama_id . ',krama_id',
            'name' => 'required|string|max:150',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',
            'email' => 'required|string|email|unique:users,email,' . $user->id, 
            'password' => 'nullable|string|min:6', 
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
        
        $validated = $validator->validated();

        try {
            DB::beginTransaction();

            $krama->update([
                'nik' => $validated['nik'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'status' => $validated['status'],
                'banjar_id' => $validated['banjar_id'],
            ]);

            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);

            DB::commit();

            return new KramaResource($krama->load('banjar', 'user'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update warga, terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * ==========================================================
     * (PERUBAHAN LOGIKA HAPUS)
     * Hapus data krama (Sesuai logika baru Anda).
     * ==========================================================
     */
    public function destroy(Krama $krama)
    {
        // 1. Cek apakah ada tagihan yang BELUM LUNAS
        $tagihanBelumLunas = $krama->tagihans()->whereDoesntHave('pembayaran')->count();

        if ($tagihanBelumLunas > 0) {
            // 2. JIKA ADA (tagihanBelumLunas > 0), HENTIKAN HAPUS
            return response()->json([
                'message' => 'Tidak bisa hapus. Krama ini masih memiliki ' . $tagihanBelumLunas . ' tagihan yang BELUM LUNAS.'
            ], 422); // 422 Unprocessable Entity
        }

        // 3. JIKA TIDAK ADA TAGIHAN BELUM LUNAS
        // (Artinya: semua lunas, atau tidak punya tagihan sama sekali)
        
        // Kita gunakan Transaction untuk keamanan
        try {
            DB::beginTransaction();

            // 4. (PENTING) Hapus semua Tagihan & Pembayaran terkait
            // Kita harus 'load' relasinya dulu
            $krama->load('tagihans.pembayaran');

            foreach ($krama->tagihans as $tagihan) {
                // Hapus pembayaran terkait (jika ada)
                if ($tagihan->pembayaran) {
                    $tagihan->pembayaran->delete();
                }
                // Hapus tagihan
                $tagihan->delete();
            }

            // 5. Hapus User (Akun Login)
            // Ini akan otomatis menghapus Krama (Warga)
            // karena 'onDelete('cascade')' pada migrasi kramas.
            if ($krama->user) {
                $krama->user->delete();
            } else {
                // Fallback jika krama (data lama) tidak punya user
                $krama->delete();
            }

            DB::commit();

            return response()->json(['message' => 'Krama, Akun, dan semua riwayat tagihan lunas berhasil dihapus.']);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal menghapus krama, terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }


    /**
     * Mengambil detail krama + semua riwayat tagihannya.
     * (Tetap Sama)
     */
    public function getHistory(Krama $krama)
    {
        $history = Tagihan::where('krama_id', $krama->krama_id)
                        ->with('pembayaran') 
                        ->latest('tanggal') 
                        ->get();

        return response()->json([
            'krama' => new KramaResource($krama->load('banjar', 'user')), 
            'history' => TagihanResource::collection($history)
        ]);
    }
}