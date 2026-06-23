<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket_teknisi', function (Blueprint $table) {
            $table->string('tiket_id', 36);
            $table->string('teknis_id', 36);
            $table->enum('peran_teknisi', ['teknisi_utama', 'teknisi_pendamping']);
            $table->timestamp('waktu_ditugaskan')->nullable();
            $table->enum('status_tugas', ['aktif', 'selesai', 'dikembalikan']);
            $table->text('alasan_dikembalikan')->nullable();

            $table->primary(['tiket_id', 'teknis_id']);
            $table->foreign('tiket_id')->references('id')->on('tiket')->cascadeOnDelete();
            $table->foreign('teknis_id')->references('id')->on('tim_teknis')->cascadeOnDelete();
            $table->index(['teknis_id', 'status_tugas']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_teknisi');
    }
};
