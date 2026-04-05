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
        Schema::create('chat_room_users', function (Blueprint $table) {
            $table->id();
            $table->string('room_id');
            $table->string('user_id');

            // optional tapi sangat berguna
            $table->enum('role_di_room', [
                'opd',
                'admin_helpdesk',
                'tim_teknis'
            ])->nullable();

            $table->timestamps();

            $table->foreign('room_id')
                ->references('id')
                ->on('chat_room')
                ->cascadeOnDelete();

            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->cascadeOnDelete();

            // biar user tidak dobel dalam 1 room
            $table->unique(['room_id', 'user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('chat_room_users');
    }
};
