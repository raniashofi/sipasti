<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tim_teknis', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('user_id')->nullable();
            $table->uuid('bidang_id')->nullable();
            $table->string('nama_lengkap')->nullable();
            $table->enum('status_teknisi', ['online', 'offline'])->default('offline');

            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('bidang_id')->references('id')->on('bidang');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tim_teknis');
    }
};
