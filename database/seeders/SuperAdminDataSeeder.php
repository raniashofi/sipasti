<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class SuperAdminDataSeeder extends Seeder
{
    private array $seedIds = [];

    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('artikel_opd_tag')->truncate();
        DB::table('sop_internal_tag')->truncate();
        DB::table('node_diagnosis')->truncate();
        DB::table('artikel_opd_rating')->truncate();
        DB::table('lampiran_artikel')->truncate();
        DB::table('artikel_opd')->truncate();
        DB::table('sop_internal')->truncate();
        DB::table('tag')->truncate();
        DB::table('kategori_artikel')->truncate();
        DB::table('kategori_sistem')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();

        $this->seedKategoriArtikel($now);
        $this->seedTags($now);
        $this->seedKnowledgeBase($now);
        $this->seedKategoriSistem();
        $this->seedNodeDiagnosis($now);
    }

    private function seedKategoriArtikel(Carbon $now): void
    {
        DB::table('kategori_artikel')->insert([
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-001'),
                'nama_kategori' => 'Aplikasi Pemerintahan',
                'deskripsi' => 'Panduan penggunaan aplikasi layanan pemerintahan dan sistem informasi OPD.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-002'),
                'nama_kategori' => 'Jaringan dan Internet',
                'deskripsi' => 'Bantuan awal untuk koneksi internet, Wi-Fi, VPN, dan akses jaringan kantor.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-003'),
                'nama_kategori' => 'Perangkat dan Printer',
                'deskripsi' => 'Panduan mandiri untuk komputer, laptop, printer, scanner, dan perangkat pendukung.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-004'),
                'nama_kategori' => 'Keamanan Informasi',
                'deskripsi' => 'Panduan keamanan akun, malware, password, email, dan perlindungan data.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-005'),
                'nama_kategori' => 'Email dan Kolaborasi',
                'deskripsi' => 'Panduan email dinas, kalender, lampiran, dan kolaborasi kerja digital.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'id' => $this->idFor('KTA', 'kategori-artikel-006'),
                'nama_kategori' => 'Data dan Backup',
                'deskripsi' => 'Panduan penyimpanan data, pencadangan, pemulihan file, dan folder bersama.',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    private function seedTags(Carbon $now): void
    {
        DB::table('tag')->insert([
            ['id' => $this->idFor('TAG', 'tag-001'), 'nama_tag' => 'akun', 'slug' => 'akun', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-002'), 'nama_tag' => 'jaringan', 'slug' => 'jaringan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-003'), 'nama_tag' => 'printer', 'slug' => 'printer', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-004'), 'nama_tag' => 'sop', 'slug' => 'sop', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-005'), 'nama_tag' => 'keamanan', 'slug' => 'keamanan', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-006'), 'nama_tag' => 'aplikasi', 'slug' => 'aplikasi', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-007'), 'nama_tag' => 'email', 'slug' => 'email', 'created_at' => $now, 'updated_at' => $now],
            ['id' => $this->idFor('TAG', 'tag-008'), 'nama_tag' => 'backup', 'slug' => 'backup', 'created_at' => $now, 'updated_at' => $now],
        ]);
    }

    private function seedKnowledgeBase(Carbon $now): void
    {
        $bidangEgov = DB::table('bidang')->where('nama_bidang', 'E-Government')->value('id');
        $bidangInfra = DB::table('bidang')->where('nama_bidang', 'Infrastruktur TI')->value('id');
        $bidangStatistik = DB::table('bidang')->where('nama_bidang', 'Statistik & Persandian')->value('id');

        $articles = [
            $this->article($this->idFor('ART', 'artikel-opd-001'), $this->idFor('KTA', 'kategori-artikel-001'), $bidangEgov, 'Cara Mengatasi Gagal Login Aplikasi Pemerintahan', 'Langkah awal saat akun OPD tidak dapat masuk ke aplikasi layanan.', '<h2>Langkah Pemeriksaan</h2><ol><li>Pastikan alamat aplikasi benar.</li><li>Periksa koneksi internet.</li><li>Gunakan fitur lupa password bila tersedia.</li><li>Hubungi helpdesk jika pesan error masih muncul.</li></ol>', 'opd', 25, 4.5, 8, [$this->idFor('TAG', 'tag-001'), $this->idFor('TAG', 'tag-006')]),
            $this->article($this->idFor('ART', 'artikel-opd-002'), $this->idFor('KTA', 'kategori-artikel-002'), $bidangInfra, 'Pemeriksaan Awal Internet Kantor Lambat', 'Checklist mandiri sebelum membuat tiket gangguan jaringan.', '<h2>Checklist</h2><ul><li>Coba akses dari perangkat lain.</li><li>Restart modem atau access point jika tersedia.</li><li>Catat lokasi, jam kejadian, dan jumlah pengguna terdampak.</li></ul>', 'opd', 41, 4.7, 11, [$this->idFor('TAG', 'tag-002')]),
            $this->article($this->idFor('ART', 'artikel-opd-003'), $this->idFor('KTA', 'kategori-artikel-003'), $bidangInfra, 'Printer Tidak Terdeteksi di Komputer', 'Panduan cek kabel, driver, antrean cetak, dan koneksi printer.', '<h2>Pemeriksaan Printer</h2><ol><li>Pastikan printer menyala.</li><li>Periksa kabel USB atau koneksi jaringan.</li><li>Hapus antrean cetak yang gagal.</li><li>Restart komputer dan printer.</li></ol>', 'opd', 33, 4.3, 6, [$this->idFor('TAG', 'tag-003')]),
            $this->article($this->idFor('ART', 'artikel-opd-004'), $this->idFor('KTA', 'kategori-artikel-004'), $bidangStatistik, 'Tindakan Awal Jika Komputer Terindikasi Malware', 'Langkah aman untuk mengurangi risiko penyebaran malware.', '<h2>Tindakan Aman</h2><ul><li>Putuskan koneksi internet.</li><li>Jangan membuka file mencurigakan.</li><li>Catat gejala yang muncul.</li><li>Buat tiket agar tim teknis dapat melakukan pemeriksaan.</li></ul>', 'opd', 19, 4.8, 5, [$this->idFor('TAG', 'tag-005')]),
            $this->article($this->idFor('ART', 'artikel-opd-005'), $this->idFor('KTA', 'kategori-artikel-005'), $bidangEgov, 'Email Dinas Tidak Bisa Mengirim atau Menerima Pesan', 'Pemeriksaan dasar untuk kendala email dinas.', '<h2>Checklist Email</h2><ul><li>Pastikan koneksi internet aktif.</li><li>Periksa kapasitas kotak masuk.</li><li>Coba login melalui webmail.</li><li>Catat pesan error yang muncul.</li></ul>', 'opd', 14, 4.4, 4, [$this->idFor('TAG', 'tag-007')]),
            $this->article($this->idFor('ART', 'artikel-opd-006'), $this->idFor('KTA', 'kategori-artikel-006'), $bidangStatistik, 'Cara Melaporkan Kehilangan atau Kerusakan Data Kerja', 'Informasi yang perlu disiapkan ketika file penting hilang atau rusak.', '<h2>Informasi yang Dibutuhkan</h2><ol><li>Nama file atau folder.</li><li>Lokasi penyimpanan terakhir.</li><li>Perkiraan waktu file terakhir normal.</li><li>Jenis perubahan atau kerusakan yang terjadi.</li></ol>', 'opd', 10, 4.2, 3, [$this->idFor('TAG', 'tag-008')]),

            $this->article($this->idFor('SOP', 'sop-internal-001'), $this->idFor('KTA', 'kategori-artikel-001'), $bidangEgov, 'SOP Reset Akun dan Verifikasi Aplikasi OPD', 'Prosedur internal helpdesk untuk validasi dan reset akun aplikasi.', '<h2>SOP Internal</h2><ol><li>Verifikasi identitas pemohon.</li><li>Periksa status akun di panel admin aplikasi.</li><li>Reset kredensial bila disetujui.</li><li>Catat tindakan di tiket.</li></ol>', 'internal', 12, 5.0, 1, [$this->idFor('TAG', 'tag-001'), $this->idFor('TAG', 'tag-004')]),
            $this->article($this->idFor('SOP', 'sop-internal-002'), $this->idFor('KTA', 'kategori-artikel-002'), $bidangInfra, 'SOP Penanganan Gangguan Jaringan Kantor', 'Prosedur internal pengecekan koneksi, switch, router, dan eskalasi ISP.', '<h2>SOP Internal</h2><ol><li>Validasi lokasi dan cakupan gangguan.</li><li>Cek perangkat jaringan utama.</li><li>Periksa log router dan switch.</li><li>Eskalasi ke ISP bila gangguan berasal dari uplink.</li></ol>', 'internal', 18, 5.0, 1, [$this->idFor('TAG', 'tag-002'), $this->idFor('TAG', 'tag-004')]),
            $this->article($this->idFor('SOP', 'sop-internal-003'), $this->idFor('KTA', 'kategori-artikel-003'), $bidangInfra, 'SOP Diagnosa Perangkat dan Printer', 'Prosedur internal pemeriksaan perangkat, driver, port, dan penggantian komponen.', '<h2>SOP Internal</h2><ol><li>Identifikasi perangkat dan nomor aset.</li><li>Uji kabel, port, dan driver.</li><li>Lakukan reinstall driver bila perlu.</li><li>Buat rekomendasi penggantian bila perangkat rusak berat.</li></ol>', 'internal', 15, 5.0, 1, [$this->idFor('TAG', 'tag-003'), $this->idFor('TAG', 'tag-004')]),
            $this->article($this->idFor('SOP', 'sop-internal-004'), $this->idFor('KTA', 'kategori-artikel-004'), $bidangStatistik, 'SOP Respons Insiden Keamanan Informasi', 'Prosedur internal isolasi, pencatatan bukti, dan pemulihan insiden keamanan.', '<h2>SOP Internal</h2><ol><li>Isolasi perangkat terdampak.</li><li>Catat indikator kompromi.</li><li>Lakukan pemindaian dan pemulihan dari backup.</li><li>Laporkan hasil penanganan.</li></ol>', 'internal', 9, 5.0, 1, [$this->idFor('TAG', 'tag-005'), $this->idFor('TAG', 'tag-008')]),
        ];

        foreach ($articles as $article) {
            $tagIds = $article['tags'];
            unset($article['tags']);

            $isOpd = $article['visibilitas_akses'] === 'opd';
            $table = $isOpd ? 'artikel_opd' : 'sop_internal';
            $tagTable = $isOpd ? 'artikel_opd_tag' : 'sop_internal_tag';
            $tagColumn = $isOpd ? 'artikel_opd_id' : 'sop_internal_id';
            unset($article['visibilitas_akses']);
            $article['judul'] = $article['nama_artikel_sop'];
            unset($article['nama_artikel_sop']);
            if ($isOpd) {
                unset($article['bidang_id']);
            } else {
                unset($article['kategori_artikel_id'], $article['rating'], $article['rating_count']);
            }

            DB::table($table)->insert($article + [
                'header_image' => 'knowledge_base/default-header.png',
                'created_at' => $now,
                'updated_at' => $now,
                'deleted_at' => null,
            ]);

            foreach ($tagIds as $tagId) {
                DB::table($tagTable)->insert([
                    $tagColumn => $article['id'],
                    'tag_id' => $tagId,
                ]);
            }
        }
    }

    private function seedKategoriSistem(): void
    {
        DB::table('kategori_sistem')->insert([
            [
                'id' => $this->idFor('KTS', 'kategori-sistem-001'),
                'nama_kategori' => 'Aplikasi Pemerintahan',
                'deskripsi' => 'Gangguan login, akses, error aplikasi, dan kendala penggunaan sistem pemerintahan.',
                'icon' => 'app',
            ],
            [
                'id' => $this->idFor('KTS', 'kategori-sistem-002'),
                'nama_kategori' => 'Jaringan Internet',
                'deskripsi' => 'Gangguan internet, Wi-Fi, LAN, VPN, dan akses server jaringan.',
                'icon' => 'network',
            ],
            [
                'id' => $this->idFor('KTS', 'kategori-sistem-003'),
                'nama_kategori' => 'Perangkat Komputer dan Printer',
                'deskripsi' => 'Masalah komputer, laptop, printer, scanner, dan perangkat kantor lain.',
                'icon' => 'default',
            ],
            [
                'id' => $this->idFor('KTS', 'kategori-sistem-004'),
                'nama_kategori' => 'Keamanan Informasi',
                'deskripsi' => 'Insiden malware, akun mencurigakan, kebocoran data, dan keamanan akses.',
                'icon' => 'shield',
            ],
        ]);
    }

    private function seedNodeDiagnosis(Carbon $now): void
    {
        $bidangEgov = DB::table('bidang')->where('nama_bidang', 'E-Government')->value('id');
        $bidangInfra = DB::table('bidang')->where('nama_bidang', 'Infrastruktur TI')->value('id');
        $bidangStatistik = DB::table('bidang')->where('nama_bidang', 'Statistik & Persandian')->value('id');

        DB::table('node_diagnosis')->insert([
            $this->solution($this->idFor('NDG', 'node-diagnosis-002'), $this->idFor('KTS', 'kategori-sistem-001'), $bidangEgov, $this->idFor('ART', 'artikel-opd-001'), $this->idFor('SOP', 'sop-internal-001'), 'Ikuti panduan reset akun dan kirim tiket jika akun tetap bermasalah.', 'Helpdesk E-Government akan memverifikasi identitas pemohon sebelum reset akun.', 'admin', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-003'), $this->idFor('KTS', 'kategori-sistem-001'), $bidangEgov, $this->idFor('ART', 'artikel-opd-001'), $this->idFor('SOP', 'sop-internal-001'), 'Laporkan error aplikasi agar helpdesk memeriksa konfigurasi sistem.', 'Sertakan nama aplikasi, pesan error, waktu kejadian, dan screenshot bila tersedia.', 'admin', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-005'), $this->idFor('KTS', 'kategori-sistem-002'), $bidangInfra, $this->idFor('ART', 'artikel-opd-002'), $this->idFor('SOP', 'sop-internal-002'), 'Gangguan jaringan massal perlu dicek oleh tim infrastruktur.', 'Lampirkan lokasi, jumlah perangkat terdampak, dan waktu mulai gangguan.', 'eskalasi', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-006'), $this->idFor('KTS', 'kategori-sistem-002'), $bidangInfra, $this->idFor('ART', 'artikel-opd-002'), $this->idFor('SOP', 'sop-internal-002'), 'Lakukan pemeriksaan koneksi perangkat, lalu ajukan tiket bila belum normal.', 'Admin helpdesk akan memandu pemeriksaan konfigurasi IP, Wi-Fi, atau kabel LAN.', 'admin', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-008'), $this->idFor('KTS', 'kategori-sistem-003'), $bidangInfra, $this->idFor('ART', 'artikel-opd-003'), $this->idFor('SOP', 'sop-internal-003'), 'Perangkat berpotensi perlu pemeriksaan langsung oleh tim teknis.', 'Jangan membongkar perangkat. Sertakan foto kondisi perangkat dan nomor aset.', 'eskalasi', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-009'), $this->idFor('KTS', 'kategori-sistem-003'), $bidangInfra, $this->idFor('ART', 'artikel-opd-003'), $this->idFor('SOP', 'sop-internal-003'), 'Ikuti pemeriksaan perangkat/printer dasar sebelum tiket diproses.', 'Admin helpdesk akan memandu pengecekan driver, kabel, port, dan antrean cetak.', 'admin', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-011'), $this->idFor('KTS', 'kategori-sistem-004'), $bidangStatistik, $this->idFor('ART', 'artikel-opd-004'), $this->idFor('SOP', 'sop-internal-004'), 'Insiden keamanan harus segera dieskalasi.', 'Putuskan koneksi jaringan perangkat terdampak dan sertakan bukti gejala.', 'eskalasi', $now),
            $this->solution($this->idFor('NDG', 'node-diagnosis-012'), $this->idFor('KTS', 'kategori-sistem-004'), $bidangStatistik, $this->idFor('ART', 'artikel-opd-004'), $this->idFor('SOP', 'sop-internal-004'), 'Laporkan kendala keamanan ringan untuk diverifikasi helpdesk.', 'Admin akan menilai apakah perlu eskalasi ke bidang statistik dan persandian.', 'admin', $now),

            $this->question($this->idFor('NDG', 'node-diagnosis-001'), $this->idFor('KTS', 'kategori-sistem-001'), 'Apakah kendala terjadi saat login atau autentikasi akun?', 'Contoh: password ditolak, akun terkunci, OTP tidak masuk, atau lupa password.', $this->idFor('NDG', 'node-diagnosis-002'), $this->idFor('NDG', 'node-diagnosis-003'), $now),
            $this->question($this->idFor('NDG', 'node-diagnosis-004'), $this->idFor('KTS', 'kategori-sistem-002'), 'Apakah gangguan jaringan dialami oleh banyak perangkat di lokasi yang sama?', 'Jika hanya satu perangkat, kemungkinan masalah konfigurasi perangkat pengguna.', $this->idFor('NDG', 'node-diagnosis-005'), $this->idFor('NDG', 'node-diagnosis-006'), $now),
            $this->question($this->idFor('NDG', 'node-diagnosis-007'), $this->idFor('KTS', 'kategori-sistem-003'), 'Apakah perangkat tidak menyala atau menunjukkan kerusakan fisik?', 'Contoh: mati total, terbakar, layar pecah, bunyi tidak normal, atau bau hangus.', $this->idFor('NDG', 'node-diagnosis-008'), $this->idFor('NDG', 'node-diagnosis-009'), $now),
            $this->question($this->idFor('NDG', 'node-diagnosis-010'), $this->idFor('KTS', 'kategori-sistem-004'), 'Apakah ada indikasi malware, akun disalahgunakan, atau data tidak dapat diakses?', 'Contoh: file terenkripsi, email terkirim sendiri, pop-up mencurigakan, atau login asing.', $this->idFor('NDG', 'node-diagnosis-011'), $this->idFor('NDG', 'node-diagnosis-012'), $now),
        ]);

    }

    private function article(string $id, ?string $kategoriId, ?string $bidangId, string $judul, string $deskripsi, string $konten, string $visibilitas, int $views, ?float $rating, int $ratingCount, array $tags): array
    {
        return [
            'id' => $id,
            'kategori_artikel_id' => $kategoriId,
            'bidang_id' => $bidangId,
            'nama_artikel_sop' => $judul,
            'deskripsi_singkat' => $deskripsi,
            'isi_konten' => $konten,
            'status_publikasi' => 'published',
            'visibilitas_akses' => $visibilitas,
            'total_views' => $views,
            'rating' => $rating,
            'rating_count' => $ratingCount,
            'tags' => $tags,
        ];
    }

    private function question(string $id, string $kategoriId, string $teks, string $hint, string $nextYa, string $nextTidak, Carbon $now): array
    {
        return [
            'id' => $id,
            'kategori_id' => $kategoriId,
            'bidang_id' => null,
            'artikel_opd_id' => null,
            'sop_internal_id' => null,
            'tipe_node' => 'pertanyaan',
            'teks_pertanyaan' => $teks,
            'hint_konteks' => $hint,
            'judul_solusi' => null,
            'penjelasan_solusi' => null,
            'rekomendasi_penanganan' => null,
            'id_next_ya' => $nextYa,
            'id_next_tidak' => $nextTidak,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function solution(string $id, string $kategoriId, string $bidangId, string $artikelOpdId, string $sopInternalId, string $judul, string $penjelasan, string $rekomendasi, Carbon $now): array
    {
        return [
            'id' => $id,
            'kategori_id' => $kategoriId,
            'bidang_id' => $bidangId,
            'artikel_opd_id' => $artikelOpdId,
            'sop_internal_id' => $sopInternalId,
            'tipe_node' => 'solusi',
            'teks_pertanyaan' => null,
            'hint_konteks' => null,
            'judul_solusi' => $judul,
            'penjelasan_solusi' => $penjelasan,
            'rekomendasi_penanganan' => $rekomendasi,
            'id_next_ya' => null,
            'id_next_tidak' => null,
            'created_at' => $now,
            'updated_at' => $now,
        ];
    }

    private function idFor(string $prefix, string $key): string
    {
        return $this->seedIds[$key] ??= IdGenerator::make($prefix);
    }
}
