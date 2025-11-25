<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    protected $primaryKey = 'pembayaran_id';

    protected $fillable = [
        'tagihan_id',
        'tgl_bayar',
        'jumlah',
        'status',
        'payment_by',
    ];

    /**
     * Relasi ke Tagihan
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    /**
     * Relasi ke User (Pembayar)
     * PENTING: Nama fungsi ini harus 'pembayar' agar sinkron dengan Controller.
     */
    public function pembayar()
    {
        return $this->belongsTo(User::class, 'payment_by');
    }
    
    // Jika ada fungsi 'adminPencatat' lama, boleh dihapus atau dibiarkan
    // tapi pastikan Controller memanggil 'pembayar', bukan 'adminPencatat'.
}