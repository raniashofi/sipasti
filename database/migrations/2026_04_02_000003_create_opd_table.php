<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('opd', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('user_id', 36);
            $table->string('kode_opd')->unique();
            $table->string('nama_opd')->nullable();
            $table->string('kdunit')->nullable();
            $table->string('parent_id', 36)->nullable();
            $table->enum('is_bagian', ['Y', 'N'])->nullable();

            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('opd')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opd');
    }
};
