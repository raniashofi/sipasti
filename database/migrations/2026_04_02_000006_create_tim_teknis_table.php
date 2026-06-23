<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_teknis', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('user_id', 36);
            $table->string('bidang_id', 36);
            $table->string('nama_lengkap')->nullable();
            $table->enum('status_teknisi', ['online', 'offline'])->default('offline');

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('bidang_id')->references('id')->on('bidang')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_teknis');
    }
};
