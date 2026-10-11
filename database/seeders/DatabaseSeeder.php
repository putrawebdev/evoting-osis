<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Data Dummy Admin
        DB::table('admin')->insert([
            [
                'username' => 'admin',
                'password_hash' => Hash::make('smpn5cibitung'),
                'role' => 'admin',
                'last_login_at' => null,
            ]
        ]);
    }
}