<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_opd_rating', function (Blueprint $table) {
            $table->string('id', 36)->primary();
            $table->string('artikel_opd_id', 36);
            $table->string('user_id', 36);
            $table->tinyInteger('rating')->unsigned();
            $table->timestamps();

            $table->foreign('artikel_opd_id')->references('id')->on('artikel_opd')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['artikel_opd_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('artikel_opd_rating');
    }
};
