<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tagihan extends Model
{
    use HasFactory;

    /**
     * Tentukan Primary Key sesuai skema Anda.
     */
    protected $primaryKey = 'tagihan_id';

    /**
     * Atribut yang dapat diisi secara massal.
     */
    protected $fillable = [
        'iuran',
        'dedosan',
        'peturuhan',
        'krama_id',
        'created_by', // ID Admin (User)
        'tanggal',
    ];

    /**
     * Relasi Many-to-One ke model Krama.
     * Satu Tagihan dimiliki oleh satu Krama.
     */
    public function krama()
    {
        return $this->belongsTo(Krama::class, 'krama_id');
    }

    /**
     * Relasi Many-to-One ke model User (Admin).
     * Satu Tagihan dibuat oleh satu Admin.
     */
    public function adminPembuat()
    {
        // 'created_by' adalah foreign key di tabel 'tagihans'
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Relasi One-to-One ke model Pembayaran.
     * Satu Tagihan memiliki satu Pembayaran.
     */
    public function pembayaran()
    {
        return $this->hasOne(Pembayaran::class, 'tagihan_id');
    }
}
