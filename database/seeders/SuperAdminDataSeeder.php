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
        DB::table('node_diagnosis')->truncate();
        DB::table('artikel_opd_rating')->truncate();
        DB::table('lampiran_artikel')->truncate();
        DB::table('artikel_opd')->truncate();
        DB::table('sop_internal')->truncate();
        DB::table('kategori_artikel')->truncate();
        DB::table('kategori_sistem')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $now = now();

        $this->seedKategoriArtikel($now);
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


    private function seedKnowledgeBase(Carbon $now): void
    {
        $bidangEgov = DB::table('bidang')->where('nama_bidang', 'E-Government')->value('id');
        $bidangInfra = DB::table('bidang')->where('nama_bidang', 'Infrastruktur TI')->value('id');
        $bidangStatistik = DB::table('bidang')->where('nama_bidang', 'Statistik & Persandian')->value('id');

        $articles = [
            $this->article(
                $this->idFor('ART', 'artikel-opd-001'),
                $this->idFor('KTA', 'kategori-artikel-001'),
                $bidangEgov,
                'Cara Mengatasi Gagal Login Aplikasi Pemerintahan',
                'Panduan pemeriksaan awal ketika akun OPD tidak dapat masuk ke aplikasi layanan.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini dipakai sebagai panduan mandiri sebelum tiket dibuat. Fokusnya adalah membedakan masalah password, akun, dan masalah aplikasi.',
                    'gejala' => [
                        'Password ditolak padahal pengguna merasa sudah benar.',
                        'Akun terkunci setelah beberapa kali percobaan login.',
                        'OTP atau kode verifikasi tidak masuk ke email atau nomor ponsel.',
                        'Muncul pesan error autentikasi, session habis, atau akses ditolak.',
                    ],
                    'langkah' => [
                        'Pastikan alamat aplikasi yang dibuka sudah benar dan versi browser masih didukung.',
                        'Coba login ulang dengan mode incognito atau browser lain untuk memastikan bukan cache yang bermasalah.',
                        'Periksa apakah akun masih aktif dan apakah OTP masuk ke kanal yang benar.',
                        'Jika password belum pernah direset, gunakan alur lupa password atau hubungi helpdesk dengan bukti identitas.',
                    ],
                    'data' => [
                        'Nama aplikasi dan alamat URL.',
                        'Email dinas / username yang digunakan.',
                        'Tangkapan layar pesan error.',
                        'Waktu kejadian dan perangkat yang dipakai.',
                    ],
                    'eskalasi' => 'Jika kegagalan login berulang setelah verifikasi, tiket perlu diteruskan ke helpdesk E-Government untuk reset akun atau pengecekan konfigurasi.',
                ]),
                'opd',
                25,
                4.6,
                8,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-002'),
                $this->idFor('KTA', 'kategori-artikel-002'),
                $bidangInfra,
                'Pemeriksaan Awal Internet Kantor Lambat',
                'Checklist pemeriksaan awal sebelum gangguan jaringan diajukan ke tim teknis.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini membantu OPD memisahkan gangguan lokal, gangguan satu perangkat, dan gangguan jaringan area.',
                    'gejala' => [
                        'Internet lambat pada semua perangkat di ruangan.',
                        'Aplikasi hanya gagal dibuka saat jam sibuk.',
                        'Wi-Fi tersambung tetapi tidak bisa akses internet.',
                        'Lampu indikator router atau access point menunjukkan status tidak normal.',
                    ],
                    'langkah' => [
                        'Coba akses dari satu perangkat lain untuk memastikan apakah gangguan terjadi secara luas.',
                        'Restart perangkat jaringan jika SOP lokal mengizinkan dan catat hasilnya.',
                        'Cek apakah kabel LAN, switch, atau access point masih aktif.',
                        'Bila gangguan terjadi di banyak titik, siapkan laporan lokasi, jam, dan jumlah user terdampak.',
                    ],
                    'data' => [
                        'Lokasi titik jaringan.',
                        'Jumlah perangkat terdampak.',
                        'Jam mulai gangguan.',
                        'Foto indikator pada modem, router, atau access point.',
                    ],
                    'eskalasi' => 'Bila gangguan meluas atau berasal dari uplink jaringan, tim infrastruktur perlu menindaklanjuti sebagai tiket eskalasi.',
                ]),
                'opd',
                41,
                4.8,
                11,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-003'),
                $this->idFor('KTA', 'kategori-artikel-003'),
                $bidangInfra,
                'Printer Tidak Terdeteksi di Komputer',
                'Panduan pemeriksaan koneksi, driver, dan antrian cetak sebelum eskalasi ke tim teknis.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini digunakan ketika printer terlihat offline, tidak muncul di komputer, atau gagal mengirim dokumen ke antrian cetak.',
                    'gejala' => [
                        'Printer menyala tetapi tidak muncul di daftar printer.',
                        'Dokumen masuk antrean namun tidak keluar kertas.',
                        'Komputer menampilkan status offline atau error driver.',
                        'Kabel USB atau koneksi jaringan terasa tidak stabil.',
                    ],
                    'langkah' => [
                        'Pastikan printer menyala dan kabel daya terpasang normal.',
                        'Periksa kabel USB atau sambungan jaringan ke printer.',
                        'Hapus dokumen yang macet di antrean cetak lalu coba kirim ulang satu halaman uji.',
                        'Restart komputer dan printer jika driver masih tidak merespons.',
                    ],
                    'data' => [
                        'Merek dan tipe printer.',
                        'Nomor aset bila ada.',
                        'Screenshot error driver atau status printer.',
                        'Lokasi penggunaan printer.',
                    ],
                    'eskalasi' => 'Jika printer tetap tidak terdeteksi setelah langkah dasar, tim teknis perlu melakukan pemeriksaan langsung di lokasi.',
                ]),
                'opd',
                33,
                4.5,
                6,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-004'),
                $this->idFor('KTA', 'kategori-artikel-004'),
                $bidangStatistik,
                'Tindakan Awal Jika Komputer Terindikasi Malware',
                'Langkah aman untuk membatasi dampak sebelum pemeriksaan keamanan dilakukan.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini menekankan isolasi cepat, pencatatan bukti, dan larangan membuka file mencurigakan.',
                    'gejala' => [
                        'File tiba-tiba terenkripsi atau tidak dapat dibuka.',
                        'Muncul pop-up yang tidak wajar atau browser diarahkan ke halaman asing.',
                        'Email terkirim sendiri dari akun pengguna.',
                        'Login dari perangkat lain terdeteksi atau kata sandi berubah tanpa izin.',
                    ],
                    'langkah' => [
                        'Putuskan koneksi internet dan jaringan lokal perangkat terdampak.',
                        'Jangan menyalin ulang file atau menjalankan aplikasi yang belum jelas sumbernya.',
                        'Catat waktu kejadian, gejala, dan tindakan terakhir sebelum insiden muncul.',
                        'Buat tiket keamanan agar tim dapat melakukan analisis dan pemulihan.',
                    ],
                    'data' => [
                        'Nama akun atau perangkat terdampak.',
                        'Cuplikan layar gejala yang muncul.',
                        'Daftar file yang terdampak atau terenkripsi.',
                        'Waktu mulai kejadian.',
                    ],
                    'eskalasi' => 'Karena risiko penyebaran tinggi, kasus seperti ini harus segera diproses sebagai insiden keamanan.',
                ]),
                'opd',
                19,
                4.9,
                5,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-005'),
                $this->idFor('KTA', 'kategori-artikel-005'),
                $bidangEgov,
                'Email Dinas Tidak Bisa Mengirim atau Menerima Pesan',
                'Pemeriksaan dasar untuk memastikan gangguan berasal dari quota, koneksi, atau konfigurasi akun.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini dipakai untuk memeriksa masalah email tanpa langsung menganggap akun rusak.',
                    'gejala' => [
                        'Pesan tidak terkirim atau masuk ke folder gagal kirim.',
                        'Kotak masuk penuh sehingga email baru tertahan.',
                        'Login webmail bisa dibuka tetapi isi pesan tidak sinkron.',
                        'Lampiran gagal diunggah atau ukuran file terlalu besar.',
                    ],
                    'langkah' => [
                        'Periksa koneksi internet dan coba akses webmail dari browser lain.',
                        'Cek kapasitas mailbox, folder sampah, dan arsip email.',
                        'Pastikan alamat tujuan benar dan lampiran tidak melebihi batas layanan.',
                        'Bila tetap gagal, lampirkan pesan error untuk ditindaklanjuti helpdesk.',
                    ],
                    'data' => [
                        'Alamat email dinas.',
                        'Contoh pesan gagal kirim atau gagal terima.',
                        'Ukuran lampiran yang dicoba dikirim.',
                        'Waktu kejadian dan browser yang digunakan.',
                    ],
                    'eskalasi' => 'Jika kendala bukan sekadar kapasitas atau koneksi, helpdesk perlu mengecek konfigurasi akun dan layanan email.',
                ]),
                'opd',
                14,
                4.6,
                4,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-006'),
                $this->idFor('KTA', 'kategori-artikel-006'),
                $bidangStatistik,
                'Cara Melaporkan Kehilangan atau Kerusakan Data Kerja',
                'Panduan melengkapi data awal agar proses pemulihan file bisa dilakukan lebih cepat.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini berisi cara mendokumentasikan kehilangan file agar tim bisa menilai apakah pemulihan dari backup masih mungkin.',
                    'gejala' => [
                        'Folder kerja mendadak kosong atau file berubah nama.',
                        'Dokumen penting tidak bisa dibuka karena corrupt.',
                        'File hilang setelah pemindahan perangkat atau reset sistem.',
                        'Versi terbaru file tidak sesuai dengan data yang terakhir dikerjakan.',
                    ],
                    'langkah' => [
                        'Jangan menimpa file lama dengan salinan baru sebelum pemeriksaan dilakukan.',
                        'Catat nama file, lokasi penyimpanan, dan waktu terakhir file terlihat normal.',
                        'Sertakan informasi siapa yang terakhir mengubah file dan dari perangkat mana.',
                        'Buat tiket dengan kronologi yang rapi agar proses recovery tidak berulang.',
                    ],
                    'data' => [
                        'Nama file atau folder.',
                        'Lokasi penyimpanan terakhir.',
                        'Perkiraan waktu terakhir file normal.',
                        'Jenis kerusakan atau perubahan yang terjadi.',
                    ],
                    'eskalasi' => 'Jika file penting tidak bisa dipulihkan dari salinan lokal, tim persandian dan infrastruktur perlu memeriksa backup atau storage bersama.',
                ]),
                'opd',
                10,
                4.4,
                3,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-007'),
                $this->idFor('KTA', 'kategori-artikel-001'),
                $bidangEgov,
                'Pemeriksaan Akses Lanjutan Saat Login Aplikasi Gagal',
                'Panduan lanjutan untuk menilai apakah masalah login berasal dari akun, kebijakan akses, atau browser.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini melengkapi panduan login dasar dengan pemeriksaan yang lebih spesifik agar helpdesk bisa membedakan kasus autentikasi, kebijakan akses, dan error aplikasi.',
                    'gejala' => [
                        'Akun bisa dipakai di aplikasi lain tetapi gagal di satu aplikasi tertentu.',
                        'Login berhasil lalu langsung logout kembali.',
                        'Muncul pesan akses ditolak karena hak pengguna belum sesuai.',
                        'Browser memunculkan redirect berulang sebelum halaman terbuka.',
                    ],
                    'langkah' => [
                        'Coba akses dengan browser yang bersih dari cache dan cookie lama.',
                        'Bandingkan hasil login dari perangkat berbeda untuk memastikan bukan masalah lokal.',
                        'Cek apakah akun baru saja dibuat, diganti peran, atau dibatasi hak aksesnya.',
                        'Jika terdapat error teknis pada tampilan, simpan detail pesan dan waktu kejadian.',
                    ],
                    'data' => [
                        'Nama aplikasi yang gagal diakses.',
                        'Peran atau jabatan pengguna di sistem.',
                        'Screenshot halaman login atau error redirect.',
                        'Riwayat singkat kapan akses terakhir berhasil.',
                    ],
                    'eskalasi' => 'Bila login tetap gagal setelah verifikasi akun, artikel ini mengarahkan helpdesk untuk meneruskan kasus ke reset akses atau pengecekan aplikasi.',
                ]),
                'opd',
                18,
                4.7,
                5,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-008'),
                $this->idFor('KTA', 'kategori-artikel-002'),
                $bidangInfra,
                'Pemeriksaan Detail Koneksi Internet Kantor yang Tidak Stabil',
                'Artikel lanjutan untuk menilai apakah gangguan jaringan berasal dari uplink, switch lokal, atau perangkat pengguna.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini dipakai ketika gangguan internet sudah dicoba langkah dasar tetapi masalah tetap muncul di banyak titik.',
                    'gejala' => [
                        'Koneksi tersambung lalu putus berulang.',
                        'Akses hanya gagal pada jam tertentu atau saat beban tinggi.',
                        'Sebagian perangkat normal tetapi sebagian lain lambat.',
                        'Status jaringan menunjukkan packet loss atau latency tinggi.',
                    ],
                    'langkah' => [
                        'Cek apakah masalah muncul di port atau titik akses yang sama.',
                        'Pisahkan gangguan pada satu pengguna dan satu area dengan tes perangkat silang.',
                        'Catat apakah internet putus total atau hanya layanan tertentu yang gagal.',
                        'Bila ada indikasi uplink atau ISP, siapkan bukti untuk eskalasi teknis.',
                    ],
                    'data' => [
                        'Lokasi titik gangguan.',
                        'Jenis koneksi yang dipakai (LAN, Wi-Fi, VPN, atau lainnya).',
                        'Jam mulai dan pola gangguan.',
                        'Bukti foto status perangkat jaringan.',
                    ],
                    'eskalasi' => 'Jika gangguan muncul di banyak perangkat dan tidak membaik setelah pengecekan lokal, kasus harus diarahkan ke tim infrastruktur.',
                ]),
                'opd',
                29,
                4.7,
                7,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-009'),
                $this->idFor('KTA', 'kategori-artikel-003'),
                $bidangInfra,
                'Pemeriksaan Lanjutan Laptop dan Printer yang Bermasalah',
                'Panduan lanjutan sebelum perangkat ditetapkan sebagai rusak berat atau perlu penggantian komponen.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini melengkapi pemeriksaan printer dan perangkat dengan fokus ke tanda kerusakan fisik, daya, dan konektivitas.',
                    'gejala' => [
                        'Perangkat hidup tetapi layar blank atau berkedip.',
                        'Printer mengeluarkan bunyi tidak normal atau kertas sering macet.',
                        'Driver sudah terpasang tetapi perangkat tetap tidak merespons.',
                        'Ada tanda panas berlebih, bau gosong, atau komponen rusak secara fisik.',
                    ],
                    'langkah' => [
                        'Pastikan catu daya dan kabel tidak longgar sebelum perangkat dinyatakan rusak.',
                        'Bandingkan hasil uji dengan perangkat atau kabel cadangan bila tersedia.',
                        'Catat apakah masalah muncul setelah listrik padam, update driver, atau pemakaian berat.',
                        'Jika ada indikasi kerusakan fisik, lakukan dokumentasi foto dan jangan membongkar perangkat.',
                    ],
                    'data' => [
                        'Merek dan tipe perangkat.',
                        'Nomor aset.',
                        'Foto kondisi fisik dan kabel.',
                        'Kronologi sebelum kerusakan muncul.',
                    ],
                    'eskalasi' => 'Artikel ini mendorong eskalasi jika perangkat tidak lagi layak ditangani lewat perbaikan dasar.',
                ]),
                'opd',
                26,
                4.4,
                5,
            ),
            $this->article(
                $this->idFor('ART', 'artikel-opd-010'),
                $this->idFor('KTA', 'kategori-artikel-004'),
                $bidangStatistik,
                'Pemeriksaan Awal dan Keamanan Lanjutan Saat Diduga Terkena Malware',
                'Panduan lanjutan untuk memastikan apakah insiden keamanan masih bersifat lokal atau sudah menyebar.',
                $this->articleBody([
                    'ringkasan' => 'Artikel ini dipakai saat gejala keamanan sudah cukup jelas tetapi tim masih perlu menentukan tingkat urgensinya.',
                    'gejala' => [
                        'Login dari lokasi asing atau perangkat asing terdeteksi.',
                        'File berubah ekstensi, terenkripsi, atau tiba-tiba tidak bisa dibuka.',
                        'Browser menampilkan situs yang bukan tujuan pengguna.',
                        'Ada tanda aktivitas yang tidak dilakukan pemilik akun.',
                    ],
                    'langkah' => [
                        'Amankan perangkat dengan memutus jaringan dan menahan sinkronisasi otomatis.',
                        'Catat akun yang terlibat dan sistem mana yang terdampak lebih dulu.',
                        'Pastikan tidak ada file baru yang disalin ke lokasi yang sama sebelum investigasi.',
                        'Kumpulkan bukti akses dan kirimkan bersama tiket keamanan.',
                    ],
                    'data' => [
                        'Waktu mulai gangguan.',
                        'Nama akun dan perangkat terdampak.',
                        'Cuplikan layar gejala malware atau login asing.',
                        'Daftar file atau layanan yang terdampak.',
                    ],
                    'eskalasi' => 'Kasus ini harus diarahkan ke tim keamanan informasi bila ada indikasi penyebaran atau penyalahgunaan akun.',
                ]),
                'opd',
                21,
                4.8,
                4,
            ),

            $this->article(
                $this->idFor('SOP', 'sop-internal-001'),
                $this->idFor('KTA', 'kategori-artikel-001'),
                $bidangEgov,
                'SOP Reset Akun dan Verifikasi Aplikasi OPD',
                'Prosedur internal helpdesk untuk validasi identitas, verifikasi akun, dan reset kredensial.',
                $this->articleBody([
                    'ringkasan' => 'SOP ini dipakai helpdesk saat login gagal, akun terkunci, atau pengguna memerlukan reset akses.',
                    'langkah' => [
                        'Verifikasi identitas pemohon dengan data dinas yang valid.',
                        'Periksa status akun pada panel admin aplikasi dan catat riwayat kegagalan login.',
                        'Reset kredensial sesuai kewenangan dan pastikan akun aktif kembali.',
                        'Sampaikan panduan login ulang dan minta pengguna uji akses sebelum tiket ditutup.',
                    ],
                    'output' => [
                        'Akun kembali aktif.',
                        'Kredensial baru sudah disampaikan secara aman.',
                        'Catatan tindakan terdokumentasi di tiket.',
                    ],
                    'catatan' => 'Jika terdapat indikasi penyalahgunaan akun, proses harus dialihkan ke pemeriksaan keamanan informasi.',
                ]),
                'internal',
                12,
                5.0,
                1,
            ),
            $this->article(
                $this->idFor('SOP', 'sop-internal-002'),
                $this->idFor('KTA', 'kategori-artikel-002'),
                $bidangInfra,
                'SOP Penanganan Gangguan Jaringan Kantor',
                'Prosedur internal pengecekan lokasi gangguan, perangkat jaringan, dan eskalasi bila diperlukan.',
                $this->articleBody([
                    'ringkasan' => 'SOP ini menuntun tim teknis untuk menilai apakah gangguan berasal dari perangkat lokal atau jaringan utama.',
                    'langkah' => [
                        'Validasi lokasi dan cakupan gangguan ke pelapor.',
                        'Cek perangkat jaringan utama seperti modem, router, switch, dan access point.',
                        'Periksa log dan indikator status perangkat untuk mencari sumber masalah.',
                        'Bila gangguan berasal dari provider atau backbone, eskalasi sesuai prosedur layanan.',
                    ],
                    'output' => [
                        'Hasil diagnosis jaringan terdokumentasi.',
                        'Tindakan pemulihan awal tercatat.',
                        'Eskalasi provider dilakukan jika dibutuhkan.',
                    ],
                    'catatan' => 'Semua pemeriksaan harus dicatat lengkap agar histori gangguan bisa dipakai saat terjadi kasus berulang.',
                ]),
                'internal',
                18,
                5.0,
                1,
            ),
            $this->article(
                $this->idFor('SOP', 'sop-internal-003'),
                $this->idFor('KTA', 'kategori-artikel-003'),
                $bidangInfra,
                'SOP Diagnosa Perangkat dan Printer',
                'Prosedur internal pemeriksaan perangkat, driver, port, koneksi, dan status kerusakan.',
                $this->articleBody([
                    'ringkasan' => 'SOP ini digunakan untuk memutuskan apakah masalah perangkat masih bisa dibantu dari jarak jauh atau harus diperbaiki langsung.',
                    'langkah' => [
                        'Identifikasi perangkat dan nomor aset sebelum melakukan pemeriksaan.',
                        'Uji kabel, port, daya, dan driver untuk memisahkan masalah hardware dan software.',
                        'Lakukan reinstall driver atau konfigurasi ulang bila perangkat masih merespons.',
                        'Jika ada tanda kerusakan berat, buat rekomendasi penggantian atau perbaikan fisik.',
                    ],
                    'output' => [
                        'Status perangkat dan driver jelas.',
                        'Keputusan lanjut remote atau onsite terdokumentasi.',
                        'Rekomendasi penggantian muncul bila komponen rusak.',
                    ],
                    'catatan' => 'Dokumentasi foto sangat disarankan saat ada indikasi kerusakan fisik.',
                ]),
                'internal',
                15,
                5.0,
                1,
            ),
            $this->article(
                $this->idFor('SOP', 'sop-internal-004'),
                $this->idFor('KTA', 'kategori-artikel-004'),
                $bidangStatistik,
                'SOP Respons Insiden Keamanan Informasi',
                'Prosedur internal isolasi, pencatatan bukti, analisis, dan pemulihan insiden keamanan.',
                $this->articleBody([
                    'ringkasan' => 'SOP ini dipakai saat ada indikasi malware, akun disalahgunakan, atau data berisiko bocor.',
                    'langkah' => [
                        'Isolasi perangkat terdampak dari jaringan dan layanan bersama.',
                        'Catat indikator kompromi, akun yang terlibat, dan urutan kejadian.',
                        'Lakukan pemindaian atau pemulihan dari backup yang sehat bila aman dilakukan.',
                        'Laporkan hasil penanganan beserta tindak lanjut pencegahannya.',
                    ],
                    'output' => [
                        'Perangkat aman kembali digunakan atau sudah dikarantina.',
                        'Bukti insiden terdokumentasi.',
                        'Tindak lanjut pencegahan tercatat.',
                    ],
                    'catatan' => 'Kasus keamanan harus diprioritaskan karena dampaknya bisa lintas perangkat dan lintas akun.',
                ]),
                'internal',
                9,
                5.0,
                1,
            ),
        ];

        foreach ($articles as $article) {
            $isOpd = $article['visibilitas_akses'] === 'opd';
            $table = $isOpd ? 'artikel_opd' : 'sop_internal';
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

        $ktsAplikasi = $this->idFor('KTS', 'kategori-sistem-001');
        $ktsJaringan = $this->idFor('KTS', 'kategori-sistem-002');
        $ktsPerangkat = $this->idFor('KTS', 'kategori-sistem-003');
        $ktsKeamanan = $this->idFor('KTS', 'kategori-sistem-004');

        $ndgLoginRoot = $this->idFor('NDG', 'node-diagnosis-001');
        $ndgLoginFollowUp = $this->idFor('NDG', 'node-diagnosis-013');
        $ndgLoginReset = $this->idFor('NDG', 'node-diagnosis-002');
        $ndgLoginError = $this->idFor('NDG', 'node-diagnosis-003');

        $ndgNetworkRoot = $this->idFor('NDG', 'node-diagnosis-004');
        $ndgNetworkFollowUp = $this->idFor('NDG', 'node-diagnosis-014');
        $ndgNetworkMass = $this->idFor('NDG', 'node-diagnosis-005');
        $ndgNetworkSingle = $this->idFor('NDG', 'node-diagnosis-006');

        $ndgDeviceRoot = $this->idFor('NDG', 'node-diagnosis-007');
        $ndgDeviceFollowUp = $this->idFor('NDG', 'node-diagnosis-015');
        $ndgDeviceRusak = $this->idFor('NDG', 'node-diagnosis-008');
        $ndgDeviceDasar = $this->idFor('NDG', 'node-diagnosis-009');

        $ndgSecurityRoot = $this->idFor('NDG', 'node-diagnosis-010');
        $ndgSecurityFollowUp = $this->idFor('NDG', 'node-diagnosis-016');
        $ndgSecurityEscalate = $this->idFor('NDG', 'node-diagnosis-011');
        $ndgSecurityReview = $this->idFor('NDG', 'node-diagnosis-012');

        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        DB::table('node_diagnosis')->insert([
            $this->question($ndgLoginRoot, $ktsAplikasi, 'Apakah kendala terjadi saat login atau autentikasi akun?', 'Contoh: password ditolak, akun terkunci, OTP tidak masuk, atau lupa password.', $ndgLoginFollowUp, $ndgLoginError, $now),
            $this->question($ndgLoginFollowUp, $ktsAplikasi, 'Apakah masalahnya lebih spesifik ke reset password, akun terkunci, atau verifikasi identitas?', 'Jika error muncul setelah login, jalur ini membantu membedakan kasus autentikasi dan kasus aplikasi.', $ndgLoginReset, $ndgLoginError, $now),
            $this->solution($ndgLoginReset, $ktsAplikasi, $bidangEgov, $this->idFor('ART', 'artikel-opd-001'), $this->idFor('SOP', 'sop-internal-001'), 'Ikuti panduan reset akun dan kirim tiket jika akun tetap bermasalah.', 'Helpdesk E-Government akan memverifikasi identitas pemohon sebelum reset akun. Sertakan NIP, nama lengkap, email dinas, dan foto/screenshot error bila ada.', 'admin', $now),
            $this->solution($ndgLoginError, $ktsAplikasi, $bidangEgov, $this->idFor('ART', 'artikel-opd-007'), $this->idFor('SOP', 'sop-internal-001'), 'Laporkan error aplikasi agar helpdesk memeriksa konfigurasi sistem.', 'Sertakan nama aplikasi, pesan error, waktu kejadian, perangkat yang dipakai, dan screenshot bila tersedia agar helpdesk bisa menilai apakah ini masalah akses, kebijakan, atau perlu eskalasi.', 'admin', $now),

            $this->question($ndgNetworkRoot, $ktsJaringan, 'Apakah gangguan jaringan dialami oleh banyak perangkat di lokasi yang sama?', 'Jika hanya satu perangkat, kemungkinan masalah konfigurasi perangkat pengguna.', $ndgNetworkFollowUp, $ndgNetworkSingle, $now),
            $this->question($ndgNetworkFollowUp, $ktsJaringan, 'Apakah koneksi putus total, lambat, atau hanya akses aplikasi tertentu yang gagal?', 'Jawaban ini membantu menentukan apakah gangguan berasal dari perangkat, akses point, atau backbone jaringan.', $ndgNetworkMass, $ndgNetworkSingle, $now),
            $this->solution($ndgNetworkMass, $ktsJaringan, $bidangInfra, $this->idFor('ART', 'artikel-opd-002'), $this->idFor('SOP', 'sop-internal-002'), 'Gangguan jaringan massal perlu dicek oleh tim infrastruktur.', 'Lampirkan lokasi, jumlah perangkat terdampak, waktu mulai gangguan, dan foto indikator perangkat jaringan agar tim infrastruktur bisa menilai cakupan masalah.', 'eskalasi', $now),
            $this->solution($ndgNetworkSingle, $ktsJaringan, $bidangInfra, $this->idFor('ART', 'artikel-opd-008'), $this->idFor('SOP', 'sop-internal-002'), 'Lakukan pemeriksaan koneksi perangkat, lalu ajukan tiket bila belum normal.', 'Admin helpdesk akan memandu pemeriksaan konfigurasi IP, Wi-Fi, atau kabel LAN. Bila hanya satu perangkat yang bermasalah, biasanya tidak perlu eskalasi massal.', 'admin', $now),

            $this->question($ndgDeviceRoot, $ktsPerangkat, 'Apakah perangkat tidak menyala atau menunjukkan kerusakan fisik?', 'Contoh: mati total, terbakar, layar pecah, bunyi tidak normal, atau bau hangus.', $ndgDeviceFollowUp, $ndgDeviceDasar, $now),
            $this->question($ndgDeviceFollowUp, $ktsPerangkat, 'Apakah masalah utamanya berada pada hardware, power, atau periferal seperti printer?', 'Pertanyaan ini memisahkan kasus kerusakan berat dari kasus yang masih bisa ditangani lewat pemeriksaan dasar.', $ndgDeviceRusak, $ndgDeviceDasar, $now),
            $this->solution($ndgDeviceRusak, $ktsPerangkat, $bidangInfra, $this->idFor('ART', 'artikel-opd-009'), $this->idFor('SOP', 'sop-internal-003'), 'Perangkat berpotensi perlu pemeriksaan langsung oleh tim teknis.', 'Jangan membongkar perangkat. Sertakan foto kondisi perangkat, nomor aset, dan kronologi kerusakan agar tim teknis dapat menentukan tindak lanjut.', 'eskalasi', $now),
            $this->solution($ndgDeviceDasar, $ktsPerangkat, $bidangInfra, $this->idFor('ART', 'artikel-opd-003'), $this->idFor('SOP', 'sop-internal-003'), 'Ikuti pemeriksaan perangkat/printer dasar sebelum tiket diproses.', 'Admin helpdesk akan memandu pengecekan driver, kabel, port, antrean cetak, dan status daya perangkat sebelum eskalasi.', 'admin', $now),

            $this->question($ndgSecurityRoot, $ktsKeamanan, 'Apakah ada indikasi malware, akun disalahgunakan, atau data tidak dapat diakses?', 'Contoh: file terenkripsi, email terkirim sendiri, pop-up mencurigakan, atau login asing.', $ndgSecurityFollowUp, $ndgSecurityReview, $now),
            $this->question($ndgSecurityFollowUp, $ktsKeamanan, 'Apakah gejalanya mengarah ke insiden aktif yang berpotensi menyebar ke perangkat lain?', 'Jika ada penyebaran, respons harus cepat dan terdokumentasi.', $ndgSecurityEscalate, $ndgSecurityReview, $now),
            $this->solution($ndgSecurityEscalate, $ktsKeamanan, $bidangStatistik, $this->idFor('ART', 'artikel-opd-010'), $this->idFor('SOP', 'sop-internal-004'), 'Insiden keamanan harus segera dieskalasi.', 'Putuskan koneksi jaringan perangkat terdampak, simpan bukti gejala, dan sertakan waktu kejadian agar tim keamanan informasi dapat melakukan investigasi.', 'eskalasi', $now),
            $this->solution($ndgSecurityReview, $ktsKeamanan, $bidangStatistik, $this->idFor('ART', 'artikel-opd-004'), $this->idFor('SOP', 'sop-internal-004'), 'Laporkan kendala keamanan ringan untuk diverifikasi helpdesk.', 'Admin akan menilai apakah perlu eskalasi ke bidang statistik dan persandian. Sertakan screenshot, email mencurigakan, atau file yang terdampak.', 'admin', $now),
        ]);

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

    }

    private function article(string $id, ?string $kategoriId, ?string $bidangId, string $judul, string $deskripsi, string $konten, string $visibilitas, int $views, ?float $rating, int $ratingCount): array
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
        ];
    }

    private function articleBody(array $sections): string
    {
        $html = '';

        if (!empty($sections['ringkasan'])) {
            $html .= '<h2>Ringkasan</h2><p>' . e($sections['ringkasan']) . '</p>';
        }

        if (!empty($sections['gejala'])) {
            $html .= '<h2>Gejala yang Sering Muncul</h2><ul>';
            foreach ($sections['gejala'] as $item) {
                $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($sections['langkah'])) {
            $html .= '<h2>Langkah Pemeriksaan</h2><ol>';
            foreach ($sections['langkah'] as $item) {
                $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ol>';
        }

        if (!empty($sections['data'])) {
            $html .= '<h2>Data yang Perlu Disiapkan</h2><ul>';
            foreach ($sections['data'] as $item) {
                $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($sections['output'])) {
            $html .= '<h2>Hasil yang Diharapkan</h2><ul>';
            foreach ($sections['output'] as $item) {
                $html .= '<li>' . e($item) . '</li>';
            }
            $html .= '</ul>';
        }

        if (!empty($sections['eskalasi'])) {
            $html .= '<h2>Kapan Harus Diteruskan</h2><p>' . e($sections['eskalasi']) . '</p>';
        }

        if (!empty($sections['catatan'])) {
            $html .= '<h2>Catatan Tambahan</h2><p>' . e($sections['catatan']) . '</p>';
        }

        return $html;
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
