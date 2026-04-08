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
        Schema::create('activity_log', function (Blueprint $table) {
            $table->id();
            $table->string('user_id')->nullable();
            $table->enum('role_pelaku', ['opd','pimpinan','super_admin','admin_helpdesk','tim_teknis']);
            $table->enum('jenis_aktivitas', ['login','logout','create','update','delete','escalate','approve','reject']);
            $table->string('detail_tindakan')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('session_id')->nullable();
            $table->timestamp('waktu_eksekusi')->nullable();
            $table->string('nama_tabel')->nullable();
            $table->string('id_record')->nullable();
            $table->json('data_before')->nullable();
            $table->json('data_after')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('activity_log');
    }
};
