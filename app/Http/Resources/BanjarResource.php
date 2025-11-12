<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BanjarResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // Format data Banjar
        return [
            'banjar_id' => $this->banjar_id,
            'nama_banjar' => $this->nama_banjar,
        ];
    }
}

