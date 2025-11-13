<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Krama extends Model
{
    use HasFactory;

    /**
     * Tentukan primary key.
     */
    protected $primaryKey = 'krama_id';

    /**
     * Tentukan field yang boleh diisi massal.
     */
    protected $fillable = [
        'nik', 
        'name', 
        'gender', 
        'status', 
        'banjar_id',
        'user_id', // <-- (PERUBAHAN) Tambahkan user_id
    ];

    /**
     * Relasi "belongsTo" (milik) ke Banjar.
     */
    public function banjar()
    {
        return $this->belongsTo(Banjar::class, 'banjar_id');
    }

    /**
     * Relasi "hasMany" (memiliki banyak) ke Tagihan.
     */
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'krama_id');
    }
    
    // --- (PERUBAHAN) Relasi 1-ke-1 ke User ---
    /**
     * Satu Krama dimiliki oleh satu User (Akun Login).
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    /**
     * FUNGSI HELPER IURAN (Tetap Sama)
     */
    public function getIuranValue(): int
    {
        // (PERUBAHAN) Tambahkan pengecekan null safety
        switch ($this->status) {
            case 'kramadesa':
                return 100000;
            case 'krama_tamiu':
            case 'tamiu':
                return 150000;
            default:
                return 0; // Default jika status null
        }
    }
}