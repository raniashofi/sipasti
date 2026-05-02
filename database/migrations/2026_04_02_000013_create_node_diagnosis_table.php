<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('node_diagnosis', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kategori_id')->nullable();
            $table->string('kb_id')->nullable();
            $table->string('sop_internal_id')->nullable();
            $table->string('bidang_id')->nullable();
            $table->enum('tipe_node', ['pertanyaan', 'solusi']);
            $table->text('teks_pertanyaan')->nullable();
            $table->text('hint_konteks')->nullable();
            $table->string('judul_solusi')->nullable();
            $table->text('penjelasan_solusi')->nullable();
            $table->enum('rekomendasi_penanganan', ['admin', 'eskalasi'])->nullable();
            $table->string('id_next_ya')->nullable();
            $table->string('id_next_tidak')->nullable();
            $table->timestamps();

            $table->foreign('kategori_id')->references('id')->on('kategori_sistem')->onDelete('cascade');
            $table->foreign('kb_id')->references('id')->on('knowledge_base');
            $table->foreign('sop_internal_id')->references('id')->on('knowledge_base')->nullOnDelete();
            $table->foreign('bidang_id')->references('id')->on('bidang')->onDelete('set null');
            $table->foreign('id_next_ya')->references('id')->on('node_diagnosis');
            $table->foreign('id_next_tidak')->references('id')->on('node_diagnosis');
            $table->index('bidang_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_diagnosis');
    }
};
