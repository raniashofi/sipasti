<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('opd_id')->nullable();
            $table->string('admin_id')->nullable();
            $table->string('kb_id')->nullable();
            $table->string('sop_internal_id')->nullable();
            $table->enum('rekomendasi_penanganan', ['admin', 'eskalasi'])->nullable();
            $table->string('bidang_id')->nullable();
            $table->string('kategori_id')->nullable();
            $table->string('subjek_masalah')->nullable();
            $table->text('detail_masalah')->nullable();
            $table->string('lokasi')->nullable();
            $table->json('foto_bukti')->nullable();
            $table->tinyInteger('penilaian')->unsigned()->nullable();
            $table->text('komentar_penutupan')->nullable();
            $table->text('spesifikasi_perangkat')->nullable();
            $table->timestamps();

            $table->foreign('opd_id')->references('id')->on('opd');
            $table->foreign('admin_id')->references('id')->on('admin_helpdesk');
            $table->foreign('kb_id')->references('id')->on('knowledge_base');
            $table->foreign('sop_internal_id')->references('id')->on('knowledge_base')->nullOnDelete();
            $table->foreign('bidang_id')->references('id')->on('bidang')->onDelete('set null');
            $table->foreign('kategori_id')->references('id')->on('kategori_sistem')->onDelete('set null');
            $table->index('bidang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket');
    }
};
