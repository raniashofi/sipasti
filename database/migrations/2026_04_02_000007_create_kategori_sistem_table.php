<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sistem', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('bidang_id')->nullable();
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable()->default('default');
            $table->foreign('bidang_id')->references('id')->on('bidang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sistem');
    }
};
