<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; 

class User extends Authenticatable
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role', 
    ];
    protected $hidden = [
        'password',
        'remember_token',
    ];
    protected $attributes = [
        'role' => 'user',
    ];
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- Relasi Admin (Tetap Sama) ---
    public function tagihanDibuat()
    {
        return $this->hasMany(Tagihan::class, 'created_by');
    }
    public function pembayaranDicatat()
    {
        return $this->hasMany(Pembayaran::class, 'payment_by');
    }

    // --- Relasi 1-ke-1 ke Krama (Tetap Sama) ---
    public function krama()
    {
        return $this->hasOne(Krama::class, 'user_id');
    }

    // --- (PERUBAHAN) Relasi 1-ke-Banyak ke Transaction ---
    /**
     * Satu User bisa memiliki banyak Transaksi (Faktur)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }
}