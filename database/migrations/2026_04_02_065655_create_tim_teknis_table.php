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
        Schema::create('tim_teknis', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('user_id')->nullable();
            $table->string('bidang_id')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->enum('status_teknisi', ['online','offline'])->nullable();

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('bidang_id')->references('id')->on('bidang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tim_teknis');
    }
};
