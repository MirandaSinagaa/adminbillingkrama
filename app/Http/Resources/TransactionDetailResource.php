<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TransactionDetailResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'transaction_detail_id' => $this->transaction_detail_id,
            'amount' => (float) $this->amount,
            // Muat info tagihan
            'tagihan' => new TagihanResource($this->whenLoaded('tagihan')),
        ];
    }
}