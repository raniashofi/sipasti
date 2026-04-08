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
        Schema::create('kategori', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('bidang_id')->nullable();
            $table->string('nama_kategori');
            $table->text('deskripsi')->nullable();
            $table->timestamps();

            $table->foreign('bidang_id')
                ->references('id')
                ->on('bidang')
                ->onDelete('set null');

            $table->index('bidang_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kategori');
    }
};
