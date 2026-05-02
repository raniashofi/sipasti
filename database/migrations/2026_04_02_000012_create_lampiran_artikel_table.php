<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lampiran_artikel', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('knowledge_base_id');
            $table->string('nama_file');
            $table->string('path_file');
            $table->string('tipe_file');
            $table->unsignedBigInteger('ukuran_file');
            $table->unsignedInteger('urutan')->default(0);
            $table->timestamps();

            $table->foreign('knowledge_base_id')->references('id')->on('knowledge_base')->onDelete('cascade');
            $table->index('knowledge_base_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lampiran_artikel');
    }
};
