<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * * LOGIKA BARU:
     * Kita HANYA menambahkan user_id.
     * Kita asumsikan kolom lain (NIK, gender, dll) sudah 'NOT NULL'
     * dari migrasi kramas sebelumnya, karena sekarang akan diisi
     * saat registrasi.
     */
    public function up(): void
    {
        Schema::table('kramas', function (Blueprint $table) {
            
            // 1. Tambah user_id (Kunci untuk sinkronisasi)
            // Kita buat unique (1 user = 1 krama).
            // Kita buat nullable() dulu agar migrasi aman
            // jika ada data krama lama yang belum punya user_id.
            $table->unsignedBigInteger('user_id')->nullable()->unique()->after('krama_id');

            // 2. Definisikan foreign key
            // onDelete('cascade') = jika user dihapus, krama-nya ikut terhapus
            $table->foreign('user_id')
                  ->references('id')
                  ->on('users')
                  ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kramas', function (Blueprint $table) {
            // Hapus foreign key dan kolom user_id
            $table->dropForeign(['user_id']);
            $table->dropColumn('user_id');
        });
    }
};