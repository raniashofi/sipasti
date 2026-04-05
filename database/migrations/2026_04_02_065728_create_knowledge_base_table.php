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
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kategori_id')->nullable();
            $table->string('nama_artikel_sop')->nullable();
            $table->text('isi_konten')->nullable();
            $table->enum('status_publikasi', ['draft','published']);
            $table->enum('visibilitas_akses', ['opd','internal']);

            $table->foreign('kategori_id')->references('id')->on('kategori');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
