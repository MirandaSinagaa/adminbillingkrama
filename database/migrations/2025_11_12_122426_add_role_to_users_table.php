<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Tambahkan kolom 'role'
            // Tipe: enum (hanya boleh 'admin' atau 'user')
            // Default: 'user' (Sesuai dengan Model User kita)
            // After: 'password' (Agar rapi di database)
            $table->enum('role', ['admin', 'user'])
                  ->default('user')
                  ->after('password');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            // Hapus kolom 'role' jika migration di-rollback
            $table->dropColumn('role');
        });
    }
};