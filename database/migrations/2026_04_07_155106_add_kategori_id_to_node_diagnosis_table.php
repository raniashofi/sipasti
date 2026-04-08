<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('node_diagnosis', function (Blueprint $table) {
            // Menambahkan kolom kategori_id bertipe UUID/String setelah kolom id
            $table->uuid('kategori_id')->nullable()->after('id');

            // Opsional tapi sangat disarankan: Buat relasi foreign key
            $table->foreign('kategori_id')
                  ->references('id')
                  ->on('kategori')
                  ->onDelete('cascade'); // Jika kategori dihapus, node di dalamnya ikut terhapus
        });
    }

    public function down()
    {
        Schema::table('node_diagnosis', function (Blueprint $table) {
            $table->dropForeign(['kategori_id']);
            $table->dropColumn('kategori_id');
        });
    }
};
