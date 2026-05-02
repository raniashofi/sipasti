<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('knowledge_base_tag', function (Blueprint $table) {
            $table->string('knowledge_base_id');
            $table->string('tag_id');
            $table->timestamps();

            $table->primary(['knowledge_base_id', 'tag_id']);

            $table->foreign('knowledge_base_id')->references('id')->on('knowledge_base')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tag')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('knowledge_base_tag');
    }
};
