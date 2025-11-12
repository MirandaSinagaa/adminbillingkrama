<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Pembayaran extends Model
{
    use HasFactory;

    /**
     * Tentukan Primary Key sesuai skema Anda.
     */
    protected $primaryKey = 'pembayaran_id';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'tagihan_id',
        'tgl_bayar',
        'jumlah',
        'status',
        'payment_by', // ID Admin (User)
    ];

    /**
     * Relasi Many-to-One ke model Tagihan.
     * Satu Pembayaran dimiliki oleh satu Tagihan.
     */
    public function tagihan()
    {
        return $this->belongsTo(Tagihan::class, 'tagihan_id');
    }

    /**
     * Relasi Many-to-One ke model User (Admin).
     * Satu Pembayaran dicatat oleh satu Admin.
     */
    public function adminPencatat()
    {
        // 'payment_by' adalah foreign key di tabel 'pembayarans'
        return $this->belongsTo(User::class, 'payment_by');
    }
}
