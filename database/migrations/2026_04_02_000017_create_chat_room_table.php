<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chat_room', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('tiket_id', 36);
            $table->string('nama_roomchat')->nullable();

            // Transfer tracking fields
            $table->boolean('is_active')->default(true)->comment('Disabled saat tiket transfer');
            $table->timestamp('transferred_at')->nullable();

            $table->timestamps();

            $table->foreign('tiket_id')->references('id')->on('tiket')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chat_room');
    }
};
