<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('opd_id', 36);
            $table->string('admin_id', 36)->nullable();
            $table->string('node_diagnosis_id', 36);
            $table->enum('rekomendasi_penanganan', ['admin', 'eskalasi'])->nullable();
            $table->unsignedTinyInteger('reopened_count')->default(1);
            $table->timestamp('last_reopened_at')->nullable();
            $table->string('subjek_masalah');
            $table->text('detail_masalah');
            $table->string('lokasi')->nullable();
            $table->tinyInteger('penilaian')->unsigned()->nullable();
            $table->text('komentar_penutupan')->nullable();
            $table->text('spesifikasi_perangkat')->nullable();
            $table->timestamps();

            $table->foreign('opd_id')->references('id')->on('opd')->onDelete('cascade');
            $table->foreign('admin_id')->references('id')->on('admin_helpdesk')->onDelete('set null');
            $table->foreign('node_diagnosis_id')->references('id')->on('node_diagnosis')->restrictOnDelete();
            $table->index('node_diagnosis_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket');
    }
};
