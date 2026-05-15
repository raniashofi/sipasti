<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_room', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('tiket_id');
            $table->string('nama_roomchat')->nullable();

            // Transfer tracking fields
            $table->boolean('is_active')->default(true)->comment('Disabled saat tiket transfer');
            $table->uuid('current_admin_id')->nullable()->comment('Admin aktif saat ini');
            $table->uuid('transferred_from_admin_id')->nullable()->comment('Admin sebelumnya saat transfer');
            $table->string('transferred_from_bidang_id')->nullable()->comment('Bidang sebelumnya saat transfer');
            $table->timestamp('transferred_at')->nullable();

            $table->timestamps();

            $table->foreign('tiket_id')->references('id')->on('tiket')->cascadeOnDelete();
            $table->foreign('current_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('transferred_from_admin_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('transferred_from_bidang_id')->references('id')->on('bidang')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room');
    }
};
