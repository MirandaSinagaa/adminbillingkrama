<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Krama; // Import Krama
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\DB; // Import DB

class AuthController extends Controller
{
    /**
     * Fungsi untuk register (Hanya untuk Role 'User')
     * (DIRUBAH TOTAL: Sekarang menerima data Krama lengkap)
     */
    public function register(Request $request)
    {
        // 1. Validasi data User + data Krama
        $validated = $request->validate([
            // Data User
            'name' => 'required|string|max:150',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
            
            // Data Krama
            'nik' => 'required|string|size:16|unique:kramas',
            'gender' => 'required|in:laki-laki,perempuan',
            'status' => 'required|in:kramadesa,krama_tamiu,tamiu',
            'banjar_id' => 'required|exists:banjars,banjar_id',
        ]);

        // 2. Gunakan DB Transaction
        try {
            DB::beginTransaction();

            // 3. Buat User
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => Hash::make($validated['password']),
                // Role 'user' otomatis dari Model User
            ]);

            // 4. Buat Krama (Data Warga) yang terhubung
            // Sekarang diisi LENGKAP
            Krama::create([
                'user_id' => $user->id,
                'name' => $validated['name'],
                'nik' => $validated['nik'],
                'gender' => $validated['gender'],
                'status' => $validated['status'],
                'banjar_id' => $validated['banjar_id'],
            ]);

            // 5. Commit transaksi
            DB::commit();

            // 6. Ambil data user yang baru dibuat (untuk auto-login)
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Register success',
                'token' => $token,       // Kirim token
                'token_type' => 'Bearer',
                'user' => $user,        // Kirim data user
            ]);

        } catch (\Exception $e) {
            // 7. Rollback jika ada error
            DB::rollBack();
            
            // Kembalikan error server
            return response()->json([
                'message' => 'Registrasi gagal, terjadi kesalahan server.',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Fungsi untuk login admin
     * (PERBAIKAN: Kirim data user saat login, BUKAN HANYA TOKEN)
     * Ini penting agar AuthContext tahu siapa yang login
     */
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        // Cari user berdasarkan email
        $user = User::where('email', $request->email)->first();

        // Cek user dan password
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Invalid credentials.'],
            ]);
        }

        // Buat token Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login success',
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user, // <-- (PERBAIKAN) Kirim data user (termasuk role)
        ]);
    }

    /**
     * Fungsi untuk mengambil data profile admin yang login.
     * (Tetap Sama)
     */
    public function profile(Request $request)
    {
        return response()->json($request->user());
    }

    /**
     * Fungsi untuk logout admin.
     * (Tetap Sama)
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout success']);
    }
}