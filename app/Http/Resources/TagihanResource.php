<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TagihanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $status = 'belum_bayar'; 
        if ($this->pembayaran) {
            $status = $this->pembayaran->status; 
        }

        // Cek apakah ada transaksi pending
        $pendingTransactionId = null;
        if ($this->transactionDetail && $this->transactionDetail->transaction) {
            if ($this->transactionDetail->transaction->status === 'pending') {
                $pendingTransactionId = $this->transactionDetail->transaction->transaction_id;
            }
        }

        return [
            'tagihan_id' => $this->tagihan_id,
            'iuran' => (float) $this->iuran,
            'dedosan' => (float) $this->dedosan,
            'peturuhan' => (float) $this->peturuhan,
            'total_tagihan' => (float) $this->iuran + (float) $this->dedosan + (float) $this->peturuhan,
            'tanggal' => $this->tanggal,
            'created_at' => $this->created_at,
            
            'krama' => new KramaResource($this->whenLoaded('krama')),
            'admin_pembuat' => $this->whenLoaded('adminPembuat', function() {
                return $this->adminPembuat->name;
            }),
            
            'status_pembayaran' => $status, 

            'dibayar_oleh' => $this->whenLoaded('pembayaran', function() {
                if ($this->pembayaran && $this->pembayaran->pembayar) {
                    return [
                        'name' => $this->pembayaran->pembayar->name,
                        'role' => $this->pembayaran->pembayar->role,
                    ];
                }
                return null;
            }),

            // (BARU) ID Transaksi jika sedang pending
            'pending_transaction_id' => $pendingTransactionId,
        ];
    }
}