<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Banjar extends Model
{
    use HasFactory;

    /**
     * Tentukan Primary Key sesuai skema Anda.
     */
    protected $primaryKey = 'banjar_id';

    /**
     * Atribut yang dapat diisi secara massal (mass assignable).
     * Sesuai migration, kita hanya perlu 'nama_banjar'.
     */
    protected $fillable = [
        'nama_banjar',
    ];

    /**
     * Relasi One-to-Many ke model Krama.
     * Satu Banjar bisa memiliki banyak Krama.
     */
    public function kramas()
    {
        // 'banjar_id' adalah foreign key di tabel 'kramas'
        return $this->hasMany(Krama::class, 'banjar_id');
    }

    /**
     * Kita nonaktifkan timestamps (created_at, updated_at) untuk Banjar
     * jika Anda merasa data ini statis (seperti daftar banjar).
     * Jika ingin tetap ada, hapus baris ini dan pastikan migrasi Anda punya $table->timestamps().
     * Sesuai rencana migrasi saya sebelumnya, kita pakai timestamps, jadi kita biarkan.
     */
    // public $timestamps = false;
}
