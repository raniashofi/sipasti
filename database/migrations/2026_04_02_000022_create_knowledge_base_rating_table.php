<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_rating', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->uuid('knowledge_base_id');
            $table->uuid('user_id');
            $table->tinyInteger('rating')->unsigned();
            $table->timestamps();

            $table->foreign('knowledge_base_id')->references('id')->on('knowledge_base')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();

            $table->unique(['knowledge_base_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_rating');
    }
};
