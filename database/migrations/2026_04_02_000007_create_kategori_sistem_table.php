<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kategori_sistem', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('nama_kategori')->unique();
            $table->text('deskripsi')->nullable();
            $table->string('icon')->nullable()->default('default');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kategori_sistem');
    }
};
