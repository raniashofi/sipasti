<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kategori_artikel_id')->nullable();
            $table->string('bidang_id')->nullable();
            $table->string('nama_artikel_sop');
            $table->text('deskripsi_singkat')->nullable();
            $table->text('isi_konten')->nullable();
            $table->string('header_image')->nullable();
            $table->string('lampiran_file')->nullable();
            $table->enum('status_publikasi', ['draft', 'published'])->default('draft');
            $table->enum('visibilitas_akses', ['opd', 'internal'])->default('opd');
            $table->unsignedInteger('total_views')->default(0);
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kategori_artikel_id')->references('id')->on('kategori_artikel')->onDelete('set null');
            $table->foreign('bidang_id')->references('id')->on('bidang')->onDelete('set null');
            $table->index('kategori_artikel_id');
            $table->index('bidang_id');
            $table->index('status_publikasi');
            $table->index('visibilitas_akses');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base');
    }
};
