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
        Schema::create('admin', function (Blueprint $table) {
            $table->id(); //[cite: 5]
            $table->string('username')->unique(); //[cite: 5]
            $table->string('password_hash'); //[cite: 5]
            $table->enum('role', ['admin', 'super_admin']); //[cite: 5]
            $table->timestamp('last_login_at')->nullable(); //[cite: 5]
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('admin');
    }
};
