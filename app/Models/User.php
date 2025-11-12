<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens; // <--- 1. TAMBAHKAN IMPORT INI

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    // 2. TAMBAHKAN HasApiTokens DI BAWAH INI (PENTING: src 901 dari modul Anda)
    use HasFactory, Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    // --- 3. TAMBAHKAN RELASI UNTUK PROYEK BILLING KITA ---

    /**
     * Relasi ke tabel Tagihan (Admin ini membuat tagihan mana saja)
     */
    public function tagihanDibuat()
    {
        // 'created_by' adalah foreign key di tabel 'tagihans'
        return $this->hasMany(Tagihan::class, 'created_by');
    }

    /**
     * Relasi ke tabel Pembayaran (Admin ini mencatat pembayaran mana saja)
     */
    public function pembayaranDicatat()
    {
        // 'payment_by' adalah foreign key di tabel 'pembayarans'
        return $this->hasMany(Pembayaran::class, 'payment_by');
    }
}
