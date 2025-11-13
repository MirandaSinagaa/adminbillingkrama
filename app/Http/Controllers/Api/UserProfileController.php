<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Models\Krama;
use App\Http\Resources\KramaResource;

class UserProfileController extends Controller
{
    /**
     * Mengambil data Krama & User yang sedang login
     * (Untuk mengisi form Edit Profil)
     */
    public function show(Request $request)
    {
        $user = Auth::user();

        // Ambil Krama yang tertaut (gunakan 'with' agar efisien)
        $krama = Krama::where('user_id', $user->id)
                      ->with('banjar', 'user')
                      ->first();

        if (!$krama) {
            return response()->json(['message' => 'Data Krama tidak ditemukan.'], 404);
        }

        return new KramaResource($krama);
    }

    /**
     * Mengupdate data Krama & User yang sedang login
     */
    public function update(Request $request)
    {
        $user = Auth::user();

        // 1. Ambil krama yang terhubung
        $krama = Krama::where('user_id', $user->id)->first();
        if (!$krama) {
            return response()->json(['message' => 'Data Krama tidak ditemukan.'], 404);
        }

        // 2. Validasi (Mirip KramaController@update)
        $validator = Validator::make($request->all(), [
            'nik' => 'required|string|size:16|unique:kramas,nik,' . $krama->krama_id . ',krama_id',
            'name' => 'required|string|max:150',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',

            // Validasi Akun
            'email' => 'required|string|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:6', // Boleh kosong
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $validated = $validator->validated();

        // 3. Gunakan Transaction
        try {
            DB::beginTransaction();

            // 4. Update data Krama (Warga)
            $krama->update([
                'nik' => $validated['nik'],
                'name' => $validated['name'],
                'gender' => $validated['gender'],
                'status' => $validated['status'],
                'banjar_id' => $validated['banjar_id'],
            ]);

            // 5. Update data User (Login)
            $userData = [
                'name' => $validated['name'],
                'email' => $validated['email'],
            ];
            if (!empty($validated['password'])) {
                $userData['password'] = Hash::make($validated['password']);
            }
            $user->update($userData);

            DB::commit();

            // 6. Kembalikan data user yang sudah di-refresh (PENTING untuk AuthContext)
            $user->refresh();
            return response()->json($user->load('krama.banjar'));

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'message' => 'Gagal update profil, terjadi kesalahan server.',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}