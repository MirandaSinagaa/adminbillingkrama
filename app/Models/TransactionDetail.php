<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TransactionDetail extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_detail_id';

    protected $fillable = [
        'transaction_id',
        'tagihan_id',
        'amount',
    ];

    /**
     * Relasi: Detail ini milik Transaksi (Faktur) mana
     */
    public function transaction()
    {
        return $this->belongsTo(Transaction::class, 'transaction_id');
    }

    /**
     * Relasi: Detail ini merujuk ke Tagihan mana
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }
}