<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\BanjarResource; 

class KramaResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $iuran = $this->getIuranValue();

        return [
            'krama_id' => $this->krama_id,
            'nik' => $this->nik,
            'name' => $this->name,
            'gender' => $this->gender,
            'status' => $this->status,
            'iuran_base' => $iuran,
            
            // Relasi Banjar (Tetap Sama)
            'banjar' => new BanjarResource($this->whenLoaded('banjar')),
            
            // (PERUBAHAN) Data Akun Login (Email)
            'email' => $this->whenLoaded('user', function () {
                return $this->user?->email;
            }),
            // (PERUBAHAN) User ID untuk referensi
            'user_id' => $this->user_id, 
        ];
    }
}