<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash; // Import Hash

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Buat 1 admin untuk login
        DB::table('users')->insert([
            'name' => 'Admin Miranda',
            'email' => 'admin@billing.com',
            'password' => Hash::make('password'), // Password-nya 'password'
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}
