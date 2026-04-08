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
        Schema::create('tiket', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('opd_id')->nullable();
            $table->string('admin_id')->nullable();
            $table->string('kb_id')->nullable();
            $table->string('subjek_masalah')->nullable();
            $table->text('detail_masalah')->nullable();
            $table->string('lokasi')->nullable();
            $table->string('foto_bukti')->nullable();
            $table->text('spesifikasi_perangkat')->nullable();
            $table->text('alasan_revisi')->nullable();
            $table->text('instruksi_khusus')->nullable();
            $table->timestamps();

            $table->foreign('opd_id')->references('id')->on('opd');
            $table->foreign('admin_id')->references('id')->on('admin_helpdesk');
            $table->foreign('kb_id')->references('id')->on('knowledge_base');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiket');
    }
};
