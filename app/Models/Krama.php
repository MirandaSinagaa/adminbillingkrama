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
        'banjar_id'
    ];

    /**
     * Relasi "belongsTo" (milik) ke Banjar.
     * Satu Krama hanya memiliki satu Banjar.
     */
    public function banjar()
    {
        return $this->belongsTo(Banjar::class, 'banjar_id');
    }

    /**
     * Relasi "hasMany" (memiliki banyak) ke Tagihan.
     * Satu Krama bisa memiliki banyak Tagihan.
     */
    public function tagihans()
    {
        return $this->hasMany(Tagihan::class, 'krama_id');
    }

    /**
     * ======================================================================
     * FUNGSI HELPER YANG HILANG (PENYEBAB ERROR 500)
     * ======================================================================
     * Accessor/Helper untuk logika Iuran (dari whiteboard).
     * KramaResource.php memanggil fungsi ini.
     */
    public function getIuranValue(): int
    {
        switch ($this->status) {
            case 'kramadesa':
                return 100000;
            case 'krama_tamiu':
            case 'tamiu':
                return 150000;
            default:
                return 0;
        }
    }
}

