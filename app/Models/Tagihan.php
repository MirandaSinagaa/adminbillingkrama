<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    protected $primaryKey = 'tagihan_id';

    protected $fillable = [
        'iuran',
        'dedosan',
        'peturuhan',
        'krama_id',
        'created_by', 
        'tanggal',
    ];

    // Relasi Krama (Tetap Sama)
    public function krama()
    {
        return $this->belongsTo(Krama::class, 'krama_id');
    }

    // Relasi AdminPembuat (Tetap Sama)
    public function adminPembuat()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // Relasi Pembayaran (Tetap Sama)
    // Ini adalah KUNCI SINKRONISASI ke Admin Panel
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }

    // --- (PERUBAHAN) Relasi 1-ke-1 ke TransactionDetail ---
    /**
     * Satu Tagihan hanya bisa ada di satu Detail Transaksi.
     */
    public function transactionDetail()
    {
        return $this->hasOne(TransactionDetail::class, 'tagihan_id');
    }
}