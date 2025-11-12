<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // \App\Models\User::factory(10)->create();

        // PANGGIL SEEDER YANG SUDAH KITA BUAT
        // Mirip dengan (src 427) di modul Anda
        $this->call([
            UserSeeder::class,
            BanjarSeeder::class,
            KramaSeeder::class,
        ]);
    }
}
