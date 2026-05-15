<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bidang', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nama_bidang')->unique();
            $table->unsignedInteger('batas_hari_pengerjaan')->default(7);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bidang');
    }
};
