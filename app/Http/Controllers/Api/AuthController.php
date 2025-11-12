<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User; // <-- Import model User
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash; // <-- Import Hash untuk enkripsi
use Illuminate\Validation\ValidationException; // <-- Import untuk error

class AuthController extends Controller
{
    /**
     * Fungsi untuk register admin baru.
     * Referensi: src 911
     */
    public function register(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string',
            'email' => 'required|string|email|unique:users',
            'password' => 'required|string|min:6',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']), // Gunakan Hash::make
        ]);

        return response()->json([
            'message' => 'Register success',
            'user' => $user,
        ]);
    }

    /**
     * Fungsi untuk login admin.
     * Referensi: src 931
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
        ]);
    }

    /**
     * Fungsi untuk mengambil data profile admin yang login.
     * Referensi: src 949
     */
    public function profile(Request $request)
    {
        // $request->user() otomatis mengambil user yang terotentikasi
        return response()->json($request->user());
    }

    /**
     * Fungsi untuk logout admin.
     * Referensi: src 958
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logout success']);
    }
}
