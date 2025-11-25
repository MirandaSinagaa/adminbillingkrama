<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Services\GoogleService;
use App\Models\Tagihan;
use Carbon\Carbon;

class GoogleIntegrasiController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function exportTagihanToSheet()
    {
        try {
            $spreadsheetId = env('GOOGLE_SHEET_ID');
            
            $data = [
                ['LAPORAN BILLING KRAMA - DI-EXPORT PADA: ' . Carbon::now()],
                ['Nama Krama', 'NIK', 'Banjar', 'Tanggal Tagihan', 'Total (Rp)', 'Status']
            ];

            // (PERBAIKAN) Load relasi 'pembayaran.pembayar' untuk cek siapa yang bayar
            $tagihans = Tagihan::with('krama.banjar', 'pembayaran.pembayar')
                                ->latest()
                                ->take(10)
                                ->get();

            foreach ($tagihans as $t) {
                $total = $t->iuran + $t->dedosan + $t->peturuhan;
                
                // (PERBAIKAN LOGIKA STATUS)
                $status = 'BELUM BAYAR';
                if ($t->pembayaran) {
                    // Cek siapa pembayarnya
                    $pembayar = $t->pembayaran->pembayar;
                    if ($pembayar) {
                        if ($pembayar->role === 'admin') {
                            $status = "LUNAS (Validasi Admin)";
                        } else {
                            $status = "LUNAS (Dibayar: {$pembayar->name})";
                        }
                    } else {
                        $status = 'LUNAS'; // Fallback jika data user terhapus
                    }
                }

                $data[] = [
                    $t->krama->name ?? 'N/A',
                    "'" . ($t->krama->nik ?? '-'), 
                    $t->krama->banjar->nama_banjar ?? '-',
                    $t->tanggal,
                    $total,
                    $status // <-- Status dinamis
                ];
            }

            $this->googleService->appendToSheet($spreadsheetId, 'Sheet1', $data);

            return response()->json([
                'message' => 'Berhasil export data ke Google Sheet!',
                'spreadsheet_url' => "https://docs.google.com/spreadsheets/d/$spreadsheetId"
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal koneksi ke Google API',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    public function testDriveUpload()
    {
        try {
            // --- (PERUBAHAN: JANGAN PAKAI ENV DULU) ---
            // Kita masukkan ID langsung di sini untuk tes
            $folderId = '1-gep3aHx3TLTb2qrDhNhrb3MEUXYKD4y'; 
            // -------------------------------------------
            
            $fileName = 'Tes_Koneksi_' . time() . '.txt';
            $content = 'Halo, ini adalah file tes dari sistem Billing Krama. Koneksi Google Drive Berhasil!';

            // Debugging: Pastikan folder ID tidak kosong
            if (empty($folderId)) {
                throw new \Exception("ID Folder Kosong! Cek kodingan.");
            }

            $result = $this->googleService->uploadFileToDrive($folderId, $fileName, $content, 'text/plain');

            return response()->json([
                'message' => 'Berhasil upload file ke Google Drive!',
                'file_id' => $result->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Gagal upload ke Google Drive',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}