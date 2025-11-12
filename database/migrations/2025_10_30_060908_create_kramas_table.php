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
        Schema::create('kramas', function (Blueprint $table) {
            $table->id('krama_id');
            $table->string('nik', 16)->unique();
            $table->string('name', 150);
            $table->enum('gender', ['laki-laki', 'perempuan']);
            $table->enum('status', ['kramadesa', 'krama_tamiu', 'tamiu']);
            $table->foreignId('banjar_id')->constrained('banjars', 'banjar_id');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kramas');
    }
};
