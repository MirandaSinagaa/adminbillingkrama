<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\Transaction;
use App\Models\Krama;
use App\Models\Tagihan;
use App\Models\Pembayaran;

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

    public function tagihanDibuat()
    {
        return $this->hasMany(Tagihan::class, 'created_by');
    }

    public function pembayaranDicatat()
    {
        return $this->hasMany(Pembayaran::class, 'payment_by');
    }

    public function krama()
    {
        return $this->hasOne(Krama::class, 'user_id');
    }

    /**
     * Satu User bisa memiliki banyak Transaksi (Faktur)
     */
    public function transactions()
    {
        return $this->hasMany(Transaction::class, 'user_id');
    }

    /**
     * Memeriksa apakah user memiliki role yang spesifik.
     *
     * @param string $role
     * @return bool
     */
    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }
}