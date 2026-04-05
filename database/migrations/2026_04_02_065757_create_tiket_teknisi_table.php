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
        Schema::create('tiket_teknisi', function (Blueprint $table) {
            $table->id();
            $table->string('tiket_id')->nullable();
            $table->string('teknis_id')->nullable();
            $table->enum('peran_teknisi', ['teknisi_utama','teknisi_pendamping']);
            $table->timestamp('waktu_ditugaskan')->nullable();
            $table->enum('status_tugas', ['menunggu','dibatalkan','aktif','selesai']);
            $table->text('alasan_dibatalkan')->nullable();

            $table->foreign('tiket_id')->references('id')->on('tiket');
            $table->foreign('teknis_id')->references('id')->on('tim_teknis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tiket_teknisi');
    }
};
