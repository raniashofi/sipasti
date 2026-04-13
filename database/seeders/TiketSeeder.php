<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class TiketSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('riwayat_transfer_tiket')->truncate();
        DB::table('status_tiket')->truncate();
        DB::table('tiket')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        // Gunakan OPD ID sesuai format OpdSeeder: 'OPD-' . strtoupper($slug)
        $tikets = [
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-BAPPEDA',
                'kategori_id'           => null, // diisi saat OPD selesai diagnosis mandiri
                'subjek_masalah'        => 'Koneksi internet tidak stabil di lantai 3',
                'detail_masalah'        => 'Selama 3 hari terakhir koneksi internet di lantai 3 gedung kantor sangat tidak stabil, sering putus dan kecepatan sangat lambat sehingga mengganggu pekerjaan.',
                'lokasi'                => 'Gedung Kantor Lantai 3, Ruang Pelayanan',
                'spesifikasi_perangkat' => 'Router TP-Link TL-ER6120, Switch Cisco SG110-16HP',
                'prioritas'             => 'sedang',
                'status_tiket'          => 'verifikasi_admin',
                'created_at'            => '2026-04-01 08:15:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-DINKES',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Komputer tidak bisa menyala (no display)',
                'detail_masalah'        => 'Komputer di meja staf administrasi tidak menampilkan gambar sama sekali. Lampu power menyala tapi layar tetap hitam. Sudah dicoba ganti kabel monitor namun hasilnya sama.',
                'lokasi'                => 'Ruang Administrasi, Meja 4',
                'spesifikasi_perangkat' => 'PC Dell OptiPlex 3070, Monitor Samsung 22 inch, RAM 8GB DDR4',
                'prioritas'             => 'tinggi',
                'status_tiket'          => 'panduan_remote',
                'created_at'            => '2026-04-02 09:30:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-BPKAD',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Aplikasi SIMDA tidak bisa login',
                'detail_masalah'        => 'Staf keuangan tidak bisa masuk ke aplikasi SIMDA sejak kemarin pagi. Muncul pesan error "Koneksi database gagal" padahal internet normal. Sudah dicoba restart aplikasi dan restart komputer tapi tetap sama.',
                'lokasi'                => 'Ruang Keuangan',
                'spesifikasi_perangkat' => 'PC Lenovo ThinkCentre M720, Windows 10 Pro, SIMDA v2.14.7',
                'prioritas'             => 'rendah',
                'status_tiket'          => 'perlu_revisi',
                'created_at'            => '2026-04-03 10:00:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-DISKOMINFO',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Printer tidak terdeteksi oleh komputer',
                'detail_masalah'        => 'Printer di ruang kepala bidang tidak bisa digunakan. Komputer tidak mendeteksi printer meski kabel USB sudah terhubung. Driver sudah diinstall ulang tapi tetap tidak terdeteksi.',
                'lokasi'                => 'Ruang Kepala Bidang TIK',
                'spesifikasi_perangkat' => 'Printer HP LaserJet Pro MFP M130fw, PC HP EliteDesk 800 G4, Windows 10',
                'prioritas'             => 'sedang',
                'status_tiket'          => 'perbaikan_teknis',
                'created_at'            => '2026-04-04 13:20:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-BKPSDM',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Laptop mati total, tidak bisa dihidupkan',
                'detail_masalah'        => 'Laptop milik kepala seksi tiba-tiba mati saat digunakan dan tidak bisa dinyalakan kembali. Sudah dicoba charger berbeda tapi tidak ada respon sama sekali. Lampu indikator tidak menyala.',
                'lokasi'                => 'Ruang Kepala Seksi Infrastruktur',
                'spesifikasi_perangkat' => 'Laptop ASUS VivoBook 15, Intel Core i5-1135G7, RAM 8GB, SSD 512GB',
                'prioritas'             => 'tinggi',
                'status_tiket'          => 'rusak_berat',
                'created_at'            => '2026-04-05 08:45:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-DISDUKCAPIL',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Email dinas tidak bisa diakses',
                'detail_masalah'        => 'Seluruh staf tidak bisa mengakses email dinas (@padang.go.id) sejak pagi hari. Halaman login tidak bisa dibuka dan muncul pesan timeout. Email personal bisa diakses normal.',
                'lokasi'                => 'Seluruh Ruangan Kantor',
                'spesifikasi_perangkat' => 'Server Email Zimbra, Semua unit komputer kantor',
                'prioritas'             => 'rendah',
                'status_tiket'          => 'selesai',
                'created_at'            => '2026-04-06 07:30:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-DPMPTSP',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Permintaan instalasi software resmi perkantoran',
                'detail_masalah'        => 'Membutuhkan instalasi Microsoft Office 365 berlisensi pada 5 unit komputer baru yang baru diterima dari pengadaan. Komputer masih belum terinstal software apapun selain Windows.',
                'lokasi'                => 'Ruang Pelayanan Informasi',
                'spesifikasi_perangkat' => '5 unit PC Acer Veriton ES2710G, Windows 11 Pro, RAM 4GB, HDD 1TB',
                'prioritas'             => 'sedang',
                'status_tiket'          => 'verifikasi_admin',
                'created_at'            => '2026-04-07 14:00:00',
            ],
            [
                'id'                    => 'TKT-' . strtoupper(Str::random(10)),
                'opd_id'                => 'OPD-BKPSDM',
                'kategori_id'           => null,
                'subjek_masalah'        => 'Komputer terindikasi terkena virus/malware',
                'detail_masalah'        => 'Komputer staf bagian kepegawaian menunjukkan perilaku aneh: muncul iklan tiba-tiba, performa sangat lambat, dan ada file yang berubah ekstensi menjadi .encrypted. Khawatir ada data penting yang terdampak.',
                'lokasi'                => 'Ruang Kepegawaian, Meja 2',
                'spesifikasi_perangkat' => 'PC Dell Inspiron 3471, Windows 10, Antivirus Avast Free (kadaluarsa)',
                'prioritas'             => 'tinggi',
                'status_tiket'          => 'perbaikan_teknis',
                'created_at'            => '2026-04-08 09:10:00',
            ],
        ];

        // status_tiket records — analisis_kerusakan kini masuk ke catatan
        $statusDetail = [
            'verifikasi_admin' => [
                0 => ['catatan' => 'Tiket diterima dan sedang dalam proses verifikasi oleh admin helpdesk.'],
                6 => ['catatan' => 'Tiket diterima. Sedang mengecek ketersediaan lisensi Microsoft Office 365 yang dialokasikan.'],
            ],
            'panduan_remote' => [
                'rekomendasi' => 'Coba lepas dan pasang kembali RAM. Jika masih sama, kemungkinan VGA card bermasalah.',
                'catatan'     => '[Analisis] Kemungkinan masalah pada VGA card atau RAM. Perlu dilakukan pengecekan remote terlebih dahulu. — Teknisi akan memandu melalui sesi remote assistance.',
            ],
            'perlu_revisi' => [
                'catatan' => 'Mohon lengkapi informasi: versi database SIMDA yang digunakan dan apakah ada update sistem yang baru dilakukan.',
            ],
            'perbaikan_teknis' => [
                3 => [
                    'rekomendasi' => 'Teknisi akan datang langsung untuk pengecekan hardware dan reinstall driver.',
                    'catatan'     => '[Analisis] Port USB pada motherboard kemungkinan bermasalah atau driver USB controller corrupt. — Jadwal kunjungan teknisi: Senin, 7 April 2026 pukul 10.00 WIB.',
                ],
                7 => [
                    'rekomendasi' => 'Komputer diisolasi dari jaringan. Teknisi keamanan akan melakukan pembersihan dan recovery data dari backup terakhir.',
                    'catatan'     => '[Analisis] Ransomware jenis LockBit 3.0 terdeteksi. Sebagian file dokumen terenkripsi. Sistem perlu isolasi dan pembersihan segera. — PENTING: Komputer harus segera dimatikan dan tidak dihubungkan ke internet sampai teknisi datang.',
                ],
            ],
            'rusak_berat' => [
                'spesifikasi_perangkat_rusak' => 'Motherboard ASUS VivoBook 15 X515EA (IC Power BQ24751A hangus)',
                'rekomendasi'                 => 'Penggantian unit laptop karena biaya perbaikan melebihi 70% harga perangkat baru. Segera proses pengadaan unit pengganti.',
                'catatan'                     => '[Analisis] Motherboard laptop mengalami kerusakan parah akibat lonjakan arus listrik. Komponen IC power management tidak dapat diperbaiki. — Laporan kerusakan telah dibuat dan dapat diunduh sebagai dasar pengadaan pengganti.',
            ],
            'selesai' => [
                'rekomendasi' => 'DNS server telah dikonfigurasi ulang. Email kini dapat diakses normal. Disarankan monitor rutin konfigurasi server email.',
                'catatan'     => '[Analisis] Konfigurasi DNS server berubah akibat pembaruan otomatis pada server, menyebabkan resolusi domain email tidak berfungsi. — Masalah berhasil diatasi pada pukul 11.30 WIB. Semua staf sudah bisa mengakses email kembali.',
            ],
        ];

        $now = now();

        foreach ($tikets as $i => $tiket) {
            $status    = $tiket['status_tiket'];
            $createdAt = $tiket['created_at'];
            $tiketId   = $tiket['id'];

            unset($tiket['status_tiket']);
            $tiket['updated_at'] = $now;

            DB::table('tiket')->insert($tiket);

            $st = match ($status) {
                'verifikasi_admin' => $i === 0
                    ? $statusDetail['verifikasi_admin'][0]
                    : $statusDetail['verifikasi_admin'][6],
                'perbaikan_teknis' => $i === 3
                    ? $statusDetail['perbaikan_teknis'][3]
                    : $statusDetail['perbaikan_teknis'][7],
                default => $statusDetail[$status],
            };

            DB::table('status_tiket')->insert([
                'id'                          => 'STS-' . strtoupper(Str::random(10)),
                'tiket_id'                    => $tiketId,
                'status_tiket'                => $status,
                'spesifikasi_perangkat_rusak' => $st['spesifikasi_perangkat_rusak'] ?? null,
                'rekomendasi'                 => $st['rekomendasi'] ?? null,
                'file_rekomendasi'            => $st['file_rekomendasi'] ?? null,
                'catatan'                     => $st['catatan'] ?? null,
                'created_at'                  => $createdAt,
            ]);
        }
    }
}
