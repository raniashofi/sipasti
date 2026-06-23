<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tiket_bukti_foto', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('tiket_id', 36);
            $table->string('foto_path');
            $table->timestamps();

            $table->foreign('tiket_id')->references('id')->on('tiket')->onDelete('cascade');
            $table->index('tiket_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tiket_bukti_foto');
    }
};
