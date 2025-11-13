<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Tagihan;
use App\Models\Transaction;
use App\Models\TransactionDetail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckoutController extends Controller
{
    /**
     * FITUR 3 (Checkout)
     * Menerima array tagihan_id, membuat Transaksi (Faktur) 'pending'.
     */
    public function store(Request $request)
    {
        // 1. Validasi input
        $validated = $request->validate([
            'tagihan_ids' => 'required|array|min:1',
            'tagihan_ids.*' => 'required|exists:tagihans,tagihan_id',
        ]);

        $user = Auth::user();
        $tagihanIds = $validated['tagihan_ids'];

        // 2. Kunci database untuk keamanan
        try {
            DB::beginTransaction();

            // 3. Ambil dan Cek semua Tagihan
            $tagihans = Tagihan::whereIn('tagihan_id', $tagihanIds)
                            ->lockForUpdate() // Kunci baris ini
                            ->get();

            $totalAmount = 0;

            foreach ($tagihans as $tagihan) {
                // Cek jika sudah dibayar (harusnya sudah dicegah oleh UserBillController)
                if ($tagihan->pembayaran) {
                    throw ValidationException::withMessages([
                        'tagihan_ids' => ['Tagihan ID ' . $tagihan->tagihan_id . ' sudah lunas.']
                    ]);
                }
                // Cek jika sudah di keranjang lain (harusnya sudah dicegah)
                if ($tagihan->transactionDetail) {
                    throw ValidationException::withMessages([
                        'tagihan_ids' => ['Tagihan ID ' . $tagihan->tagihan_id . ' sedang diproses di transaksi lain.']
                    ]);
                }
                // Hitung total
                $totalAmount += ($tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan);
            }

            // (DEBUG) Pastikan jumlah tagihan yang ditemukan = jumlah yang diminta
            if (count($tagihans) != count($tagihanIds)) {
                 throw ValidationException::withMessages([
                        'tagihan_ids' => ['Beberapa tagihan tidak valid atau sudah diproses.']
                    ]);
            }

            // 4. Buat 1 Transaksi (Faktur) Induk
            $transaction = Transaction::create([
                'user_id' => $user->id,
                'total_amount' => $totalAmount,
                'status' => 'pending', // Status awal
                'payment_method' => $request->input('payment_method', 'QRIS'), // Ambil dari request
            ]);

            // 5. Buat Rincian Transaksi (Isi Keranjang)
            foreach ($tagihans as $tagihan) {
                TransactionDetail::create([
                    'transaction_id' => $transaction->transaction_id,
                    'tagihan_id' => $tagihan->tagihan_id,
                    'amount' => ($tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan)
                ]);
            }

            // 6. (OPSIONAL) Integrasi Payment Gateway (misal Midtrans)
            // Di sini Anda akan memanggil Midtrans, mendapatkan Snap Token
            // $transaction->update(['payment_token' => $snapToken]);

            // 7. Commit
            DB::commit();

            // Kembalikan data Transaksi yang baru dibuat
            return response()->json($transaction->load('details.tagihan'), 201); // 201 Created

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], ($e instanceof ValidationException ? 422 : 500));
        }
    }
}