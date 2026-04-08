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
            $table->string('nama_artikel_sop');
            $table->text('deskripsi_singkat')->nullable();
            $table->text('isi_konten');

            // Gambar
            $table->string('header_image')->nullable();

            // Status & Visibility
            $table->enum('status_publikasi', ['draft', 'published'])->default('draft');
            $table->enum('visibilitas_akses', ['opd', 'internal'])->default('opd');

            // Statistics
            $table->unsignedInteger('total_views')->default(0);
            $table->decimal('rating', 3, 1)->nullable()->comment('Rating 0-5');

            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kategori_id')->references('id')->on('kategori')->onDelete('set null');
            $table->index('kategori_id');
            $table->index('status_publikasi');
            $table->index('visibilitas_akses');
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
