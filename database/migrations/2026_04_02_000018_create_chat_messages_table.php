<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_messages', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('room_id');
            $table->string('sender_id');
            $table->text('konten')->nullable();
            $table->string('file_url')->nullable();
            $table->enum('tipe_konten', ['text', 'image', 'file']);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('room_id')->references('id')->on('chat_room')->cascadeOnDelete();
            $table->foreign('sender_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_messages');
    }
};
