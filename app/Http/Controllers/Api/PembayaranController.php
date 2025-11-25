<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use App\Services\GoogleService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Http\Resources\PembayaranResource;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf; // <-- Import Library PDF

class PembayaranController extends Controller
{
    protected $googleService;

    public function __construct(GoogleService $googleService)
    {
        $this->googleService = $googleService;
    }

    public function index()
    {
        $pembayarans = Pembayaran::with('tagihan.krama', 'pembayar')->latest()->paginate(10);
        return PembayaranResource::collection($pembayarans);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tagihan_id' => 'required|exists:tagihans,tagihan_id',
        ]);

        // Load relasi lengkap untuk PDF
        $tagihan = Tagihan::with('krama.banjar')->find($validated['tagihan_id']);

        if ($tagihan->pembayaran) {
            throw ValidationException::withMessages(['tagihan_id' => ['Tagihan ini sudah lunas.']]);
        }

        $totalTagihan = $tagihan->iuran + $tagihan->dedosan + $tagihan->peturuhan;

        // 1. Simpan Pembayaran ke DB
        $pembayaran = Pembayaran::create([
            'tagihan_id' => $validated['tagihan_id'],
            'tgl_bayar' => Carbon::now(),
            'jumlah' => $totalTagihan,
            'status' => 'selesai',
            'payment_by' => Auth::id(),
        ]);

        // Reload relasi untuk PDF
        $pembayaran->load('tagihan.krama.banjar', 'pembayar');

        // --- (BARU) GENERATE PDF & UPLOAD KE DRIVE ---
        try {
            // a. Buat PDF dari View
            $pdf = Pdf::loadView('pdf.faktur', ['pembayaran' => $pembayaran]);
            $pdfContent = $pdf->output(); // Ambil isi file PDF (binary)

            // b. Buat Nama File Unik
            $namaFile = 'FAKTUR_' . $tagihan->krama->name . '_' . time() . '.pdf';
            $folderId = env('GOOGLE_DRIVE_FOLDER_ID');

            // c. Upload ke Drive via GoogleService
            $this->googleService->uploadFileToDrive($folderId, $namaFile, $pdfContent, 'application/pdf');

            // (Opsional) Log sukses
            \Log::info("Berhasil upload faktur ke Drive: $namaFile");

        } catch (\Exception $e) {
            \Log::error("Gagal upload PDF ke Drive: " . $e->getMessage());
            // Jangan hentikan proses jika upload gagal, cukup catat log
        }

        // --- (BARU) SYNC KE GOOGLE SHEET (Code sebelumnya) ---
        try {
            $spreadsheetId = env('GOOGLE_SHEET_ID');
            $data = [[
                $tagihan->krama->name ?? 'N/A',
                "'" . ($tagihan->krama->nik ?? '-'),
                $tagihan->krama->banjar->nama_banjar ?? '-',
                Carbon::now()->format('Y-m-d H:i:s'),
                $totalTagihan,
                'LUNAS (Validasi Admin)'
            ]];
            $this->googleService->appendToSheet($spreadsheetId, 'Sheet1', $data);
        } catch (\Exception $e) { /* Silent fail */ }

        return new PembayaranResource($pembayaran);
    }

    public function show(Pembayaran $pembayaran)
    {
        return new PembayaranResource($pembayaran->load('tagihan.krama', 'pembayar'));
    }
    public function update(Request $request, Pembayaran $pembayaran)
    {
        $pembayaran->update($request->validate(['tgl_bayar'=>'sometimes|date','status'=>'sometimes|in:pending,selesai']));
        return new PembayaranResource($pembayaran->load('tagihan', 'pembayar'));
    }
    public function destroy(Pembayaran $pembayaran)
    {
        $pembayaran->delete();
        return response()->json(['message' => 'Data pembayaran berhasil dihapus']);
    }
}