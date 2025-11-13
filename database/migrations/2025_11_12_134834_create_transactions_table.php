<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Ini adalah tabel "Faktur" atau "Induk Checkout".
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id('transaction_id'); // Primary Key

            // Relasi ke user_id (Siapa yang MEMBAYAR)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

            // Info Pembayaran
            $table->decimal('total_amount', 15, 2); // Total yang harus dibayar
            $table->enum('status', ['pending', 'paid', 'failed', 'expired'])->default('pending');

            // Info Payment Gateway (kita simulasikan)
            $table->string('payment_method')->nullable(); // Misal: 'QRIS'
            $table->string('payment_token')->nullable(); // Token dari Midtrans/Xendit
            $table->string('payment_url')->nullable(); // URL bayar

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};