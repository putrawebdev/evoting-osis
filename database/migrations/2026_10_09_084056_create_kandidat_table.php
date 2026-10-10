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
        Schema::create('kandidat', function (Blueprint $table) {
            $table->id(); //[cite: 4]
            $table->integer('no_paslon')->unique(); //[cite: 4]
            $table->string('nama_ketua'); //[cite: 4]
            $table->string('nama_wakil'); //[cite: 4]
            $table->string('foto'); //[cite: 4]
            $table->text('visi'); //[cite: 4]
            $table->text('misi'); //[cite: 4]
            $table->text('proggram'); //[cite: 4]
            $table->timestamps(); //[cite: 4]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kandidat');
    }
};
