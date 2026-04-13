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
        Schema::create('riwayat_transfer_tiket', function (Blueprint $table) {
            $table->id();
            $table->string('tiket_id');
            $table->string('pengirim_admin_id');        // Admin / Teknisi yang mentransfer
            $table->string('penerima_admin_id')->nullable();  // Admin tujuan (nullable jika dilempar ke bidang)
            $table->string('penerima_bidang_id')->nullable(); // Bidang tujuan (nullable jika langsung ke orang)
            $table->text('alasan_transfer')->nullable();      // Alasan / instruksi_khusus perpindahan
            $table->timestamp('waktu_transfer')->useCurrent();

            $table->foreign('tiket_id')->references('id')->on('tiket');
            $table->foreign('pengirim_admin_id')->references('id')->on('admin_helpdesk');
            $table->foreign('penerima_admin_id')->references('id')->on('admin_helpdesk');
            $table->foreign('penerima_bidang_id')->references('id')->on('bidang');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('riwayat_transfer_tiket');
    }
};
