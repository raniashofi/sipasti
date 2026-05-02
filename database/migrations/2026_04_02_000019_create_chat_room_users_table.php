<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('user_id');
            $table->enum('role_di_room', ['opd', 'admin_helpdesk', 'tim_teknis'])->nullable();
            $table->timestamp('last_read_at')->nullable();

            $table->foreign('room_id')->references('id')->on('chat_room')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['room_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};
