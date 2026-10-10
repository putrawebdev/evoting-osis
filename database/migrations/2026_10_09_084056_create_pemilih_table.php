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
        Schema::create('pemilih', function (Blueprint $table) {
            $table->id(); //[cite: 4]
            $table->string('nis')->unique(); //[cite: 4]
            $table->string('nama'); //[cite: 4]
            $table->string('kelas'); //[cite: 4]
            // Foreign key paslon_dipilih, nullable karena awalnya belum milih
            $table->foreignId('paslon_dipilih')->nullable()->constrained('kandidat')->nullOnDelete(); //[cite: 4]
            $table->boolean('sudah_memilih')->default(false); //[cite: 4]
            $table->timestamp('waktu_memilih')->nullable(); //[cite: 4]
            $table->timestamps(); //[cite: 4]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pemilih');
    }
};
