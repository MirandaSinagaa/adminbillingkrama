<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB

class KramaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mirip dengan ProductSeeder Anda (src 413)
        DB::table('kramas')->insert([
            [
                'nik' => '1234567890123456',
                'name' => 'I Wayan Krama Desa',
                'gender' => 'laki-laki',
                'status' => 'kramadesa', // Akan dapat iuran 100k
                'banjar_id' => 1, // Banjar Suka
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nik' => '6543210987654321',
                'name' => 'Ni Luh Krama Tamiu',
                'gender' => 'perempuan',
                'status' => 'krama_tamiu', // Akan dapat iuran 150k
                'banjar_id' => 2, // Banjar Duka
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nik' => '9876543210123456',
                'name' => 'Komang Tamiu',
                'gender' => 'laki-laki',
                'status' => 'tamiu', // Akan dapat iuran 150k
                'banjar_id' => 1, // Banjar Suka
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);
    }
}
