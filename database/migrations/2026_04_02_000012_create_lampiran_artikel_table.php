<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_artikel', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('artikel_opd_id', 36)->nullable();
            $table->string('sop_internal_id', 36)->nullable();
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('tipe_file');
            $table->unsignedBigInteger('ukuran_file');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->foreign('artikel_opd_id')->references('id')->on('artikel_opd')->cascadeOnDelete();
            $table->foreign('sop_internal_id')->references('id')->on('sop_internal')->cascadeOnDelete();
            $table->index('artikel_opd_id');
            $table->index('sop_internal_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_artikel');
    }
};
