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
        Schema::create('riwayat', function (Blueprint $table) {
            $table->id();
            $table->string('aktor'); // Menyimpan nama admin atau NIS siswa
            $table->string('aksi');  // Kategori aksi: "Memilih", "Login Admin", "Kandidat", dll
            $table->text('detail')->nullable(); // Penjelasan detail, misal: "Menambah paslon 1"
            
            // Ini akan otomatis membuat kolom 'created_at' dan 'updated_at'
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat');
    }
};
