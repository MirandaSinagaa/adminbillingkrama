<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Http\Resources\TagihanResource; // Kita gunakan lagi TagihanResource

class PembayaranResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'pembayaran_id' => $this->pembayaran_id,
            'tgl_bayar' => $this->tgl_bayar,
            'jumlah' => (float) $this->jumlah,
            'status' => $this->status,
            'dicatat_oleh_admin' => $this->whenLoaded('adminPencatat', function() {
                return $this->adminPencatat->name;
            }),
            'tagihan' => new TagihanResource($this->whenLoaded('tagihan')),
        ];
    }
}
