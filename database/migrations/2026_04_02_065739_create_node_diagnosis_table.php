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
        Schema::create('node_diagnosis', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->string('kb_id')->nullable();
            $table->enum('tipe_node', ['pertanyaan','solusi']);
            $table->text('teks_pertanyaan')->nullable();
            $table->text('hint_konteks')->nullable();
            $table->string('judul_solusi')->nullable();
            $table->text('penjelasan_solusi')->nullable();
            $table->enum('prioritas', ['rendah','sedang','tinggi'])->nullable();
            $table->timestamps();

            $table->string('id_next_ya')->nullable();
            $table->string('id_next_tidak')->nullable();

            $table->foreign('kb_id')->references('id')->on('knowledge_base');
            $table->foreign('id_next_ya')->references('id')->on('node_diagnosis');
            $table->foreign('id_next_tidak')->references('id')->on('node_diagnosis');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('node_diagnosis');
    }
};
