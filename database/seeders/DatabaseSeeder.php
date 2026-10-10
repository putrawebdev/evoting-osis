<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Data Dummy Kandidat (Paslon)
        DB::table('kandidat')->insert([
            [
                'no_paslon' => 1,
                'nama_ketua' => 'Ara',
                'nama_wakil' => 'Wafi',
                'foto' => 'paslon1.jpg',
                'visi' => 'Mewujudkan OSIS yang interaktif dan inovatif.',
                'misi' => '1. Mengadakan event digital. 2. Meningkatkan partisipasi siswa.',
                'proggram' => 'Pekan Olahraga Digital & Pensi Tahunan', // Tambahkan ini
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'no_paslon' => 2,
                'nama_ketua' => 'Mahda',
                'nama_wakil' => 'Dejaaa',
                'foto' => 'paslon2.jpg',
                'visi' => 'Membangun karakter siswa yang kreatif dan peduli lingkungan.',
                'misi' => '1. Program penghijauan sekolah. 2. Lomba kreativitas antar kelas.',
                'proggram' => 'Jumat Bersih & Workshop Daur Ulang', // Tambahkan ini
                'created_at' => now(),
                'updated_at' => now(),
            ]
        ]);

        // 2. Data Dummy Pemilih (Siswa)
        DB::table('pemilih')->insert([
            [
                'nis' => '10101',
                'nama' => 'Daka',
                'kelas' => 'XI PPLG 1',
                'paslon_dipilih' => null, // Belum memilih
                'sudah_memilih' => false,
                'waktu_memilih' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'nis' => '10102',
                'nama' => 'Siswa Tester 2',
                'kelas' => 'XI PPLG 1',
                'paslon_dipilih' => null,
                'sudah_memilih' => false,
                'waktu_memilih' => null,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        // 3. Data Dummy Admin
        DB::table('admin')->insert([
            [
                'username' => 'admin_osis',
                'password_hash' => Hash::make('password123'),
                'role' => 'admin',
                'last_login_at' => null,
            ]
        ]);
    }
}