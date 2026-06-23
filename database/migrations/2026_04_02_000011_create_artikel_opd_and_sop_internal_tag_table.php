<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('artikel_opd_tag', function (Blueprint $table) {
            $table->string('artikel_opd_id', 36);
            $table->string('tag_id', 36);

            $table->primary(['artikel_opd_id', 'tag_id']);

            $table->foreign('artikel_opd_id')->references('id')->on('artikel_opd')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tag')->onDelete('cascade');
        });

        Schema::create('sop_internal_tag', function (Blueprint $table) {
            $table->string('sop_internal_id', 36);
            $table->string('tag_id', 36);

            $table->primary(['sop_internal_id', 'tag_id']);

            $table->foreign('sop_internal_id')->references('id')->on('sop_internal')->onDelete('cascade');
            $table->foreign('tag_id')->references('id')->on('tag')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sop_internal_tag');
        Schema::dropIfExists('artikel_opd_tag');
    }
};
