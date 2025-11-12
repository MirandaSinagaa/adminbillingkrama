<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB; // Import DB

class BanjarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Mirip dengan CategorySeeder Anda (src 399)
        DB::table('banjars')->insert([
            ['nama_banjar' => 'Banjar Suka', 'created_at' => now(), 'updated_at' => now()],
            ['nama_banjar' => 'Banjar Duka', 'created_at' => now(), 'updated_at' => now()],
            ['nama_banjar' => 'Banjar Loka', 'created_at' => now(), 'updated_at' => now()],
        ]);
    }
}
