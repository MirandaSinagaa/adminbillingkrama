<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Transaction;
use App\Models\Pembayaran;
use App\Services\GoogleService;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Import Library PDF

class PaymentConfirmationController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'transaction_id' => 'required|exists:transactions,transaction_id'
        ]);

        $transactionId = $validated['transaction_id'];

        try {
            DB::beginTransaction();

            $transaction = Transaction::with('user', 'details.tagihan.krama.banjar')
                                ->lockForUpdate()
                                ->findOrFail($transactionId);

            if ($transaction->status == 'paid') {
                return response()->json(['message' => 'Transaksi ini sudah dibayar.'], 422);
            }

            $transaction->update(['status' => 'paid']);

            $sheetData = [];
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

            foreach ($transaction->details as $detail) {
                $tagihan = $detail->tagihan;
                
                if ($tagihan && !$tagihan->pembayaran) {
                    $totalTagihan = $tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan;
                    
                    // 1. Buat Pembayaran
                    $pembayaran = Pembayaran::create([
                        'tagihan_id' => $tagihan->tagihan_id,
                        'tgl_bayar' => Carbon::now(),
                        'jumlah' => $totalTagihan,
                        'status' => 'selesai',
                        'payment_by' => $transaction->user_id,
                    ]);
                    
                    // Reload agar relasi lengkap untuk PDF
                    $pembayaran->load('tagihan.krama.banjar', 'pembayar');

                    // --- (BARU) GENERATE PDF & UPLOAD KE DRIVE ---
                    // Kita upload per tagihan agar arsip rapi per warga
                    try {
                        $pdf = Pdf::loadView('pdf.faktur', ['pembayaran' => $pembayaran]);
                        $pdfContent = $pdf->output();
                        $namaFile = 'FAKTUR_USER_' . $tagihan->krama->name . '_' . time() . '.pdf';
                        
                        $this->googleService->uploadFileToDrive($folderId, $namaFile, $pdfContent, 'application/pdf');
                    } catch (\Exception $e) {
                        \Log::error("Gagal upload PDF User ke Drive: " . $e->getMessage());
                    }
                    // -------------------------------------------

                    $namaPembayar = $transaction->user->name ?? 'User';
                    $statusText = "LUNAS (Dibayar: $namaPembayar)";

                    $sheetData[] = [
                        $tagihan->krama->name ?? 'N/A',
                        "'" . ($tagihan->krama->nik ?? '-'),
                        $tagihan->krama->banjar->nama_banjar ?? '-',
                        Carbon::now()->format('Y-m-d H:i:s'),
                        $totalTagihan,
                        $statusText
                    ];
                }
            }
            
            DB::commit();

            // Sync ke Sheet
            try {
                if (!empty($sheetData)) {
                    $spreadsheetId = env('GOOGLE_SHEET_ID');
                    $this->googleService->appendToSheet($spreadsheetId, 'Sheet1', $sheetData);
                }
            } catch (\Exception $e) { \Log::error('Gagal auto-sync ke Sheet: ' . $e->getMessage()); }

            return response()->json([
                'message' => 'Pembayaran berhasil! Faktur PDF telah diarsipkan ke Google Drive.',
                'transaction' => $transaction->load('details.tagihan') 
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }
}