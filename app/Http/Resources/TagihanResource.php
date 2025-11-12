<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // ==========================================================
        //         (INI ADALAH PERBAIKAN LOGIKA)
        // ==========================================================
        
        // Controller Anda (TagihanController@index) sudah me-load relasi 'pembayaran'.
        // Kita cek di sini.
        // $this->pembayaran akan 'null' jika tidak ada pembayaran (belum bayar).
        // $this->pembayaran akan berisi 'objek' jika sudah ada pembayaran.
        
        $status = 'belum_bayar'; // Default
        if ($this->pembayaran) {
            // Jika ada objek pembayaran, kita ambil status DARI PEMBAYARAN ITU
            $status = $this->pembayaran->status; // (e.g., 'pending' atau 'selesai')
        }

        return [
            'tagihan_id' => $this->tagihan_id,
            'iuran' => (float) $this->iuran,
            'dedosan' => (float) $this->dedosan,
            'peturuhan' => (float) $this->peturuhan,
            'total_tagihan' => (float) $this->iuran + (float) $this->dedosan + (float) $this->peturuhan,
            'tanggal' => $this->tanggal,
            'created_at' => $this->created_at,
            
            // Relasi
            'krama' => new KramaResource($this->whenLoaded('krama')),
            'admin_pembuat' => $this->whenLoaded('adminPembuat', function() {
                return $this->adminPembuat->name;
            }),
            
            // (DIUBAH) Kirim status yang sudah kita proses di atas
            'status_pembayaran' => $status, 
        ];
    }
}