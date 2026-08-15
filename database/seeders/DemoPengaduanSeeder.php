<?php

namespace Database\Seeders;

use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DemoPengaduanSeeder extends Seeder
{
    public function run(): void
    {
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('chat_messages')->truncate();
        DB::table('chat_room_users')->truncate();
        DB::table('chat_room')->truncate();
        DB::table('tiket_bukti_foto')->truncate();
        DB::table('tiket_teknisi')->truncate();
        DB::table('status_tiket')->truncate();
        DB::table('tiket')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $opdId = DB::table('opd')->where('kode_opd', '9.99.0.00.0.00.00.0001')->value('id');
        $this->ensureValue($opdId, 'OPD contoh1 tidak ditemukan');

        $adminEgov = $this->ensureValue(
            DB::table('admin_helpdesk')->where('nama_lengkap', 'Admin Helpdesk E-Gov 1')->value('id'),
            'Admin Helpdesk E-Gov 1 tidak ditemukan'
        );
        $adminInfra = $this->ensureValue(
            DB::table('admin_helpdesk')->where('nama_lengkap', 'Admin Helpdesk Infrastruktur 1')->value('id'),
            'Admin Helpdesk Infrastruktur 1 tidak ditemukan'
        );
        $adminStatistik = $this->ensureValue(
            DB::table('admin_helpdesk')->where('nama_lengkap', 'Admin Helpdesk Statistik 1')->value('id'),
            'Admin Helpdesk Statistik 1 tidak ditemukan'
        );

        $teknisInfra = $this->ensureValue(
            DB::table('tim_teknis')->where('nama_lengkap', 'Tim Teknis Infrastruktur IT 1')->value('id'),
            'Tim Teknis Infrastruktur IT 1 tidak ditemukan'
        );
        $teknisStatistik = $this->ensureValue(
            DB::table('tim_teknis')->where('nama_lengkap', 'Tim Teknis Statistik & Persandian 1')->value('id'),
            'Tim Teknis Statistik & Persandian 1 tidak ditemukan'
        );

        $tickets = [
            [
                'subject' => 'Akun aplikasi dinas belum bisa masuk',
                'detail' => 'Pengguna baru melaporkan akun contoh1 belum dapat login ke aplikasi layanan internal meski password sudah dicek ulang.',
                'location' => 'Kantor utama lantai 2',
                'specification' => 'Laptop Windows 11, browser Chrome terbaru',
                'solution_title' => 'Ikuti panduan reset akun dan kirim tiket jika akun tetap bermasalah.',
                'admin_id' => null,
                'recommendation' => 'admin',
                'statuses' => ['verifikasi_admin'],
                'created_at' => Carbon::now()->subDays(7),
            ],
            [
                'subject' => 'Permintaan revisi pengaduan jaringan',
                'detail' => 'Tim helpdesk meminta tambahan informasi karena lokasi titik gangguan belum lengkap.',
                'location' => 'Ruang pelayanan publik',
                'specification' => 'Router kantor dan switch lantai 1',
                'solution_title' => 'Gangguan jaringan massal perlu dicek oleh tim infrastruktur.',
                'admin_id' => $adminInfra,
                'recommendation' => 'eskalasi',
                'statuses' => ['verifikasi_admin', 'perlu_revisi'],
                'created_at' => Carbon::now()->subDays(6),
            ],
            [
                'subject' => 'Koneksi internet kantor lambat saat buka aplikasi',
                'detail' => 'Gangguan hanya muncul di beberapa perangkat dan bisa diuji via panduan remote.',
                'location' => 'Front office',
                'specification' => 'PC shared service, Wi-Fi kantor',
                'solution_title' => 'Lakukan pemeriksaan koneksi perangkat, lalu ajukan tiket bila belum normal.',
                'admin_id' => $adminInfra,
                'recommendation' => 'admin',
                'statuses' => ['verifikasi_admin', 'panduan_remote'],
                'created_at' => Carbon::now()->subDays(5),
            ],
            [
                'subject' => 'Printer tidak terdeteksi di komputer layanan',
                'detail' => 'Driver sudah dicek, tetapi printer masih tidak muncul di antrian cetak dan perlu pengecekan langsung.',
                'location' => 'Ruang operator',
                'specification' => 'Printer jaringan Epson L5190',
                'solution_title' => 'Ikuti pemeriksaan perangkat/printer dasar sebelum tiket diproses.',
                'admin_id' => $adminInfra,
                'recommendation' => 'admin',
                'statuses' => ['verifikasi_admin', 'perbaikan_teknis'],
                'created_at' => Carbon::now()->subDays(4),
                'teknisi_id' => $teknisInfra,
            ],
            [
                'subject' => 'Laptop staf mati total setelah lonjakan listrik',
                'detail' => 'Perangkat tidak menyala, lampu indikator mati, dan terdapat aroma gosong ringan di sisi adaptor.',
                'location' => 'Ruang staf administrasi',
                'specification' => 'Laptop kerja Lenovo ThinkPad',
                'solution_title' => 'Perangkat berpotensi perlu pemeriksaan langsung oleh tim teknis.',
                'admin_id' => $adminInfra,
                'recommendation' => 'eskalasi',
                'statuses' => ['verifikasi_admin', 'perbaikan_teknis', 'rusak_berat'],
                'created_at' => Carbon::now()->subDays(3),
                'teknisi_id' => $teknisInfra,
                'penilaian' => 2,
                'komentar_penutupan' => 'Perangkat dinyatakan perlu penggantian komponen utama.',
            ],
            [
                'subject' => 'Email dinas tidak bisa mengirim file lampiran',
                'detail' => 'Setelah panduan remote, konfigurasi ulang berhasil dan pesan keluar kembali normal.',
                'location' => 'Subbagian umum',
                'specification' => 'Webmail dinas dan laptop kerja',
                'solution_title' => 'Laporkan kendala keamanan ringan untuk diverifikasi helpdesk.',
                'admin_id' => $adminStatistik,
                'recommendation' => 'admin',
                'statuses' => ['verifikasi_admin', 'panduan_remote', 'selesai'],
                'created_at' => Carbon::now()->subDays(2),
                'penilaian' => 4,
                'komentar_penutupan' => 'Tiket dinyatakan selesai setelah pengujian pengiriman berhasil.',
            ],
            [
                'subject' => 'Gangguan akses jaringan muncul lagi setelah sempat normal',
                'detail' => 'Pengaduan sebelumnya selesai, tetapi setelah dua hari koneksi kembali terputus pada jam sibuk.',
                'location' => 'Layanan front desk',
                'specification' => 'Modem dan access point lantai 1',
                'solution_title' => 'Gangguan jaringan massal perlu dicek oleh tim infrastruktur.',
                'admin_id' => $adminInfra,
                'recommendation' => 'eskalasi',
                'statuses' => ['verifikasi_admin', 'panduan_remote', 'selesai', 'dibuka_kembali'],
                'created_at' => Carbon::now()->subDay(),
                'teknisi_id' => $teknisInfra,
                'reopened_count' => 2,
                'last_reopened_at' => Carbon::now()->subHours(10),
            ],
            [
                'subject' => 'Insiden keamanan sudah diverifikasi dan ditutup',
                'detail' => 'Hasil pemeriksaan menunjukkan tidak ada aktivitas berbahaya, sehingga tiket ditutup oleh OPD setelah konfirmasi.',
                'location' => 'Ruang arsip digital',
                'specification' => 'Akun email dinas dan folder bersama',
                'solution_title' => 'Insiden keamanan harus segera dieskalasi.',
                'admin_id' => $adminStatistik,
                'recommendation' => 'eskalasi',
                'statuses' => ['verifikasi_admin', 'perbaikan_teknis', 'selesai', 'tiket_ditutup'],
                'created_at' => Carbon::now()->subHours(8),
                'teknisi_id' => $teknisStatistik,
                'penilaian' => 5,
                'komentar_penutupan' => 'Konfirmasi OPD diterima dan tiket resmi ditutup.',
            ],
        ];

        foreach ($tickets as $index => $ticket) {
            $nodeId = $this->solutionNodeId($ticket['solution_title']);
            $bidangId = DB::table('node_diagnosis')->where('id', $nodeId)->value('bidang_id');
            $ticketId = IdGenerator::make('TKT', true);

            DB::table('tiket')->insert([
                'id' => $ticketId,
                'opd_id' => $opdId,
                'admin_id' => $ticket['admin_id'],
                'bidang_id' => $bidangId,
                'node_diagnosis_id' => $nodeId,
                'rekomendasi_penanganan' => $ticket['recommendation'],
                'reopened_count' => $ticket['reopened_count'] ?? 1,
                'last_reopened_at' => $ticket['last_reopened_at'] ?? null,
                'subjek_masalah' => $ticket['subject'],
                'detail_masalah' => $ticket['detail'],
                'lokasi' => $ticket['location'],
                'penilaian' => $ticket['penilaian'] ?? null,
                'komentar_penutupan' => $ticket['komentar_penutupan'] ?? null,
                'spesifikasi_perangkat' => $ticket['specification'],
                'created_at' => $ticket['created_at'],
                'updated_at' => $ticket['created_at'],
            ]);

            foreach ($ticket['statuses'] as $statusIndex => $status) {
                $statusTime = $ticket['created_at']->copy()->addHours($statusIndex * 6);

                DB::table('status_tiket')->insert([
                    'id' => IdGenerator::make('STS', true),
                    'tiket_id' => $ticketId,
                    'status_tiket' => $status,
                    'spesifikasi_perangkat_rusak' => in_array($status, ['perbaikan_teknis', 'rusak_berat'], true) ? $ticket['specification'] : null,
                    'rekomendasi' => $ticket['recommendation'],
                    'catatan' => $this->statusNote($status, $ticket['subject']),
                    'file_bukti' => $status === 'rusak_berat' ? 'tiket/bukti-rusak-berat.jpg' : null,
                    'created_at' => $statusTime,
                    'updated_at' => $statusTime,
                ]);
            }

            if (isset($ticket['teknisi_id'])) {
                DB::table('tiket_teknisi')->insert([
                    'tiket_id' => $ticketId,
                    'teknis_id' => $ticket['teknisi_id'],
                    'peran_teknisi' => 'teknisi_utama',
                    'waktu_ditugaskan' => $ticket['created_at']->copy()->addHours(2),
                    'status_tugas' => in_array('tiket_ditutup', $ticket['statuses'], true) ? 'selesai' : 'aktif',
                    'alasan_dikembalikan' => null,
                ]);
            }

            if (in_array('rusak_berat', $ticket['statuses'], true)) {
                DB::table('tiket_bukti_foto')->insert([
                    'id' => IdGenerator::make('TBF', true),
                    'tiket_id' => $ticketId,
                    'foto_path' => 'tiket/foto/' . ($index + 1) . '-kerusakan.jpg',
                    'created_at' => $ticket['created_at']->copy()->addHour(),
                    'updated_at' => $ticket['created_at']->copy()->addHour(),
                ]);
            }
        }
    }

    private function solutionNodeId(string $title): string
    {
        $nodeId = DB::table('node_diagnosis')
            ->where('tipe_node', 'solusi')
            ->where('judul_solusi', $title)
            ->value('id');

        return $this->ensureValue($nodeId, 'Node diagnosis solusi tidak ditemukan: ' . $title);
    }

    private function statusNote(string $status, string $subject): string
    {
        return match ($status) {
            'verifikasi_admin' => 'Tiket "' . $subject . '" sedang diverifikasi helpdesk.',
            'perlu_revisi' => 'OPD diminta melengkapi informasi pada tiket "' . $subject . '".',
            'panduan_remote' => 'Admin helpdesk memberi panduan remote pada tiket "' . $subject . '".',
            'perbaikan_teknis' => 'Tiket "' . $subject . '" diteruskan ke tim teknis.',
            'rusak_berat' => 'Hasil pemeriksaan menunjukkan kerusakan berat pada tiket "' . $subject . '".',
            'selesai' => 'Penanganan tiket "' . $subject . '" sudah selesai menunggu konfirmasi.',
            'dibuka_kembali' => 'OPD membuka kembali tiket "' . $subject . '" karena kendala masih muncul.',
            'tiket_ditutup' => 'Tiket "' . $subject . '" sudah ditutup.',
            default => 'Status tiket diperbarui.',
        };
    }

    private function ensureValue(mixed $value, string $message): mixed
    {
        if ($value === null || $value === '') {
            throw new \RuntimeException($message);
        }

        return $value;
    }
}
