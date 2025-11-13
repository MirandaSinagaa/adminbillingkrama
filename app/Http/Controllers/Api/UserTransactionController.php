<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Transaction;
use App\Http\Resources\TransactionResource; // <-- Gunakan Resource baru

class UserTransactionController extends Controller
{
    /**
     * Mengambil semua riwayat transaksi (checkout) milik user.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $transactions = Transaction::where('user_id', $user->id)
                            ->with('details.tagihan.krama.banjar') // <-- Kunci: Load semua relasi
                            ->latest() // Urutkan terbaru di atas
                            ->paginate(10); // Paginasi

        return TransactionResource::collection($transactions);
    }
}