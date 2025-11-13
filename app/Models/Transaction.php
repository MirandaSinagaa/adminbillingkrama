<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    use HasFactory;

    protected $primaryKey = 'transaction_id';

    protected $fillable = [
        'user_id',
        'total_amount',
        'status',
        'payment_method',
        'payment_token',
        'payment_url',
    ];

    /**
     * Relasi: Transaksi ini milik siapa (User Pembayar)
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * Relasi: Rincian (isi keranjang) dari transaksi ini
     */
    public function details()
    {
        return $this->hasMany(TransactionDetail::class, 'transaction_id');
    }
}