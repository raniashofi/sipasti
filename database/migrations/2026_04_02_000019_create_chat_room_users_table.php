<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->uuid('room_id');
            $table->uuid('user_id');
            $table->enum('role_di_room', ['opd', 'admin_helpdesk', 'tim_teknis'])->nullable();
            $table->timestamp('last_read_at')->nullable();

            // Admin tracking fields (untuk multi-admin history)
            $table->integer('sequence_number')->nullable()->comment('Urutan admin (1,2,3...) - hanya untuk admin_helpdesk');
            $table->timestamp('started_at')->nullable()->comment('Waktu admin mulai handle tiket');
            $table->timestamp('ended_at')->nullable()->comment('Waktu admin transfer/selesai');
            $table->boolean('is_active')->default(false)->comment('Admin saat ini aktif atau history');

            $table->primary(['room_id', 'user_id']);

            $table->foreign('room_id')->references('id')->on('chat_room')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};
