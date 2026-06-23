<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_opd', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('kategori_artikel_id', 36);
            $table->string('judul');
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('isi_konten')->nullable()->comment('LONGTEXT untuk support base64-encoded images');
            $table->string('header_image')->nullable();
            $table->enum('status_publikasi', ['draft', 'published'])->default('draft');
            $table->unsignedInteger('total_views')->default(0);
            $table->decimal('rating', 3, 1)->nullable();
            $table->unsignedInteger('rating_count')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('kategori_artikel_id')->references('id')->on('kategori_artikel')->restrictOnDelete();
            $table->index('kategori_artikel_id');
            $table->index('status_publikasi');
        });

        Schema::create('sop_internal', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('bidang_id', 36);
            $table->string('judul');
            $table->text('deskripsi_singkat')->nullable();
            $table->longText('isi_konten')->nullable()->comment('LONGTEXT untuk support base64-encoded images');
            $table->string('header_image')->nullable();
            $table->enum('status_publikasi', ['draft', 'published'])->default('draft');
            $table->unsignedInteger('total_views')->default(0);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('bidang_id')->references('id')->on('bidang')->restrictOnDelete();
            $table->index('bidang_id');
            $table->index('status_publikasi');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_internal');
        Schema::dropIfExists('artikel_opd');
    }
};
