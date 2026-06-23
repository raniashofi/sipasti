<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_diagnosis', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('kategori_id', 36);
            $table->string('bidang_id', 36)->nullable();
            $table->string('artikel_opd_id', 36)->nullable();
            $table->string('sop_internal_id', 36)->nullable();
            $table->enum('tipe_node', ['pertanyaan', 'solusi']);
            $table->text('teks_pertanyaan')->nullable();
            $table->text('hint_konteks')->nullable();
            $table->string('judul_solusi')->nullable();
            $table->text('penjelasan_solusi')->nullable();
            $table->enum('rekomendasi_penanganan', ['admin', 'eskalasi'])->nullable();
            $table->string('id_next_ya', 36)->nullable();
            $table->string('id_next_tidak', 36)->nullable();
            $table->timestamps();

            $table->foreign('kategori_id')->references('id')->on('kategori_sistem')->cascadeOnDelete();
            $table->foreign('bidang_id')->references('id')->on('bidang')->onDelete('set null');
            $table->foreign('artikel_opd_id')->references('id')->on('artikel_opd')->nullOnDelete();
            $table->foreign('sop_internal_id')->references('id')->on('sop_internal')->nullOnDelete();
            $table->foreign('id_next_ya')->references('id')->on('node_diagnosis');
            $table->foreign('id_next_tidak')->references('id')->on('node_diagnosis');
            $table->index('bidang_id');
            $table->index('artikel_opd_id');
            $table->index('sop_internal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_diagnosis');
    }
};
