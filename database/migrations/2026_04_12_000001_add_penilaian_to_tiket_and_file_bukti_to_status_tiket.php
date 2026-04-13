<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Tambah kolom penilaian & komentar ke tabel tiket
        Schema::table('tiket', function (Blueprint $table) {
            $table->tinyInteger('penilaian')->unsigned()->nullable()->after('foto_bukti');
            $table->text('komentar_penutupan')->nullable()->after('penilaian');
        });

        // Tambah kolom file_bukti ke tabel status_tiket
        Schema::table('status_tiket', function (Blueprint $table) {
            $table->string('file_bukti')->nullable()->after('catatan');
        });

        // Tambah nilai 'dibuka_kembali' ke enum status_tiket
        DB::statement("ALTER TABLE status_tiket MODIFY COLUMN status_tiket ENUM(
            'verifikasi_admin','perlu_revisi','panduan_remote',
            'perbaikan_teknis','rusak_berat','selesai','dibuka_kembali'
        ) NOT NULL");
    }

    public function down(): void
    {
        Schema::table('tiket', function (Blueprint $table) {
            $table->dropColumn(['penilaian', 'komentar_penutupan']);
        });

        Schema::table('status_tiket', function (Blueprint $table) {
            $table->dropColumn('file_bukti');
        });

        DB::statement("ALTER TABLE status_tiket MODIFY COLUMN status_tiket ENUM(
            'verifikasi_admin','perlu_revisi','panduan_remote',
            'perbaikan_teknis','rusak_berat','selesai'
        ) NOT NULL");
    }
};
