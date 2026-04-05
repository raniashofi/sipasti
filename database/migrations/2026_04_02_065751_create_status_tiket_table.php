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
        Schema::create('status_tiket', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('tiket_id')->nullable();
            $table->enum('status_tiket', [
                'verifikasi_admin','perlu_revisi','panduan_remote',
                'perbaikan_teknis','rusak_berat','selesai'
            ]);
            $table->text('analisis_kerusakan')->nullable();
            $table->string('spesifikasi_perangkat_rusak')->nullable();
            $table->string('rekomendasi')->nullable();
            $table->string('file_rekomendasi')->nullable();
            $table->text('catatan')->nullable();

            $table->foreign('tiket_id')->references('id')->on('tiket');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('status_tiket');
    }
};
