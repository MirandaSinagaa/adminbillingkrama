<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ini adalah tabel "Isi Keranjang" dari setiap faktur.
     */
    public function up(): void
    {
        Schema::create('transaction_details', function (Blueprint $table) {
            $table->id('transaction_detail_id');

            // Relasi ke tabel Induk (Transaction)
            $table->foreignId('transaction_id')->constrained('transactions', 'transaction_id')->onDelete('cascade');

            // Relasi ke Tagihan (Tagihan mana yang dibayar)
            $table->foreignId('tagihan_id')->constrained('tagihans', 'tagihan_id')->onDelete('cascade');

            // (Opsional) Simpan jumlah saat itu, untuk arsip
            $table->decimal('amount', 15, 2); 

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_details');
    }
};