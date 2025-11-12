<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BanjarResource; // <-- INI DIA BARIS YANG HILANG

class KramaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Mendapatkan nilai iuran dari model
        $iuran = $this->getIuranValue();

        // Format data Krama
        return [
            'krama_id' => $this->krama_id,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'status' => $this->status,
            'iuran_base' => $iuran, // Menampilkan iuran dasar
            
            // Relasi Banjar
            // Ini adalah baris 25 yang menyebabkan error.
            // Sekarang akan berfungsi karena 'use' statement di atas
            'banjar' => new BanjarResource($this->whenLoaded('banjar')),
        ];
    }
}

