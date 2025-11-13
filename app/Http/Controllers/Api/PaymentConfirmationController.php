<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Pembayaran; // <-- Kunci Sinkronisasi
use Illuminate\Support\Facades\DB;
use Carbon\Carbon; // Import Carbon

class PaymentConfirmationController extends Controller
{
    /**
     * FITUR 4 (Sinkronisasi)
     * Ini adalah simulasi Webhook / Tombol "Saya Sudah Bayar".
     * Ini akan mengkonfirmasi Transaksi DAN membuat record Pembayaran
     * agar Admin Panel ikut terupdate.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,transaction_id'
        ]);

        $transactionId = $validated['transaction_id'];

        try {
            DB::beginTransaction();

            // 1. Ambil Transaksi, kunci, dan muat relasi Rincian (Tagihan)
            $transaction = Transaction::with('details.tagihan')
                            ->lockForUpdate()
                            ->findOrFail($transactionId);

            // 2. Cek apakah sudah dibayar
            if ($transaction->status == 'paid') {
                return response()->json(['message' => 'Transaksi ini sudah dibayar.'], 422);
            }

            // 3. Ubah status Transaksi (Faktur)
            $transaction->update([
                'status' => 'paid',
            ]);

            // 4. (KUNCI UTAMA) Buat record Pembayaran (untuk sinkronisasi Admin)
            foreach ($transaction->details as $detail) {
                $tagihan = $detail->tagihan;

                // Cek lagi (walaupun kecil kemungkinan)
                if ($tagihan && !$tagihan->pembayaran) {

                    // Hitung total per tagihan (bukan dari $detail->amount agar aman)
                    $totalTagihan = $tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan;

                    // Buat record Pembayaran (SAMA SEPERTI PembayaranController)
                    Pembayaran::create([
                        'tagihan_id' => $tagihan->tagihan_id,
                        'tgl_bayar' => Carbon::now(),
                        'jumlah' => $totalTagihan,
                        'status' => 'selesai',
                        'payment_by' => $transaction->user_id, // Dibayar oleh User
                    ]);
                }
            }

            DB::commit();

            return response()->json([
                'message' => 'Pembayaran berhasil dikonfirmasi. Status di Admin Panel telah Lunas.',
                'transaction' => $transaction->load('details.tagihan') // Kirim data final
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}