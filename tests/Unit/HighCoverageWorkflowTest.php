<?php

namespace Tests\Unit;

use App\Events\NewChatMessage;
use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\AdminHelpdesk\ChatController as AdminChatController;
use App\Http\Controllers\AdminHelpdesk\DashboardController as AdminDashboardController;
use App\Http\Controllers\AdminHelpdesk\ManajemenTiketController as AdminManajemenTiketController;
use App\Http\Controllers\AdminHelpdesk\PustakaController as AdminPustakaController;
use App\Http\Controllers\Opd\BantuanController;
use App\Http\Controllers\Opd\ChatController as OpdChatController;
use App\Http\Controllers\Opd\DiagnosisMandiriController;
use App\Http\Controllers\Opd\DashboardController as OpdDashboardController;
use App\Http\Controllers\Opd\PengaduanSayaController;
use App\Http\Controllers\Pimpinan\DashboardController as PimpinanDashboardController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\KnowledgeBaseController;
use App\Http\Controllers\SuperAdmin\KonfigurasiSistemController;
use App\Http\Controllers\SuperAdmin\ManajemenPenggunaController;
use App\Http\Controllers\TimTeknis\AntreanController as TimTeknisAntreanController;
use App\Http\Controllers\TimTeknis\ChatController as TimTeknisChatController;
use App\Http\Controllers\TimTeknis\DashboardController as TimTeknisDashboardController;
use App\Http\Controllers\UserProfileController;
use App\Http\Middleware\CheckRole;
use App\Models\AdminHelpdesk;
use App\Models\ArtikelOpd;
use App\Models\Bidang;
use App\Models\ChatMessage;
use App\Models\ChatRoom;
use App\Models\KategoriArtikel;
use App\Models\KategoriSistem;
use App\Models\LampiranArtikel;
use App\Models\NodeDiagnosis;
use App\Models\Opd;
use App\Models\Pimpinan;
use App\Models\SopInternal;
use App\Models\StatusTiket;
use App\Models\Tiket;
use App\Models\TiketBuktiFoto;
use App\Models\TiketTeknisi;
use App\Models\TimTeknis;
use App\Models\User;
use App\Notifications\StatusTiketNotification;
use App\Notifications\TiketMasukNotification;
use App\Notifications\TiketTransferNotification;
use App\Notifications\TugasBaruNotification;
use App\Support\IdGenerator;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;
use Tests\TestCase;

class HighCoverageWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $superAdmin;
    private User $opdUser;
    private User $adminUser;
    private User $teknisiUser;
    private User $pimpinanUser;
    private Bidang $bidang;
    private Opd $opd;
    private AdminHelpdesk $admin;
    private TimTeknis $teknisi;
    private KategoriSistem $kategoriSistem;
    private KategoriArtikel $kategoriArtikel;
    private ArtikelOpd $artikel;
    private SopInternal $sop;
    private NodeDiagnosis $node;
    private Tiket $tiket;

    protected function setUp(): void
    {
        parent::setUp();

        Carbon::setTestNow(Carbon::parse('2026-05-24 10:00:00'));

        $this->superAdmin = $this->user('super_admin', 'super@example.test');
        $this->opdUser = $this->user('opd', 'opd@example.test');
        $this->adminUser = $this->user('admin_helpdesk', 'admin@example.test');
        $this->teknisiUser = $this->user('tim_teknis', 'teknisi@example.test');
        $this->pimpinanUser = $this->user('pimpinan', 'pimpinan@example.test');

        $this->bidang = Bidang::create([
            'nama_bidang' => 'Infrastruktur',
            'batas_hari_pengerjaan' => 3,
        ]);

        $this->opd = Opd::create([
            'user_id' => $this->opdUser->id,
            'kode_opd' => 'OPD-01',
            'nama_opd' => 'Dinas Contoh',
            'kdunit' => '01',
            'is_bagian' => 'N',
        ]);

        $this->admin = AdminHelpdesk::create([
            'user_id' => $this->adminUser->id,
            'bidang_id' => $this->bidang->id,
            'nama_lengkap' => 'Admin Helpdesk',
        ]);

        $this->teknisi = TimTeknis::create([
            'user_id' => $this->teknisiUser->id,
            'bidang_id' => $this->bidang->id,
            'nama_lengkap' => 'Teknisi Utama',
            'status_teknisi' => 'online',
        ]);

        Pimpinan::create([
            'user_id' => $this->pimpinanUser->id,
            'nama_lengkap' => 'Pimpinan',
        ]);

        $this->kategoriArtikel = KategoriArtikel::create([
            'nama_kategori' => 'Jaringan',
            'deskripsi' => 'Panduan jaringan',
        ]);

        $this->artikel = ArtikelOpd::create([
            'kategori_artikel_id' => $this->kategoriArtikel->id,
            'judul' => 'Reset koneksi',
            'deskripsi_singkat' => 'Langkah reset koneksi',
            'isi_konten' => '<h2>Langkah</h2><p>Restart perangkat.</p>',
            'status_publikasi' => 'published',
            'total_views' => 4,
            'rating' => 4.5,
            'rating_count' => 2,
        ]);

        $this->sop = SopInternal::create([
            'bidang_id' => $this->bidang->id,
            'judul' => 'SOP Perbaikan Jaringan',
            'deskripsi_singkat' => 'SOP internal',
            'isi_konten' => '<p>Periksa kabel dan router.</p>',
            'status_publikasi' => 'published',
            'total_views' => 3,
        ]);

        $this->kategoriSistem = KategoriSistem::create([
            'nama_kategori' => 'Aplikasi',
            'deskripsi' => 'Masalah aplikasi',
            'icon' => 'monitor',
        ]);

        $this->node = NodeDiagnosis::create([
            'kategori_id' => $this->kategoriSistem->id,
            'bidang_id' => $this->bidang->id,
            'artikel_opd_id' => $this->artikel->id,
            'sop_internal_id' => $this->sop->id,
            'tipe_node' => 'solusi',
            'judul_solusi' => 'Solusi koneksi',
            'penjelasan_solusi' => 'Ikuti panduan',
            'rekomendasi_penanganan' => 'eskalasi',
        ]);

        $this->tiket = Tiket::create([
            'opd_id' => $this->opd->id,
            'admin_id' => $this->admin->id,
            'node_diagnosis_id' => $this->node->id,
            'rekomendasi_penanganan' => 'eskalasi',
            'subjek_masalah' => 'Internet lambat',
            'detail_masalah' => 'Koneksi sangat lambat sejak pagi',
            'lokasi' => 'Gedung A',
            'spesifikasi_perangkat' => 'Router utama',
            'penilaian' => 5,
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        StatusTiket::create([
            'tiket_id' => $this->tiket->id,
            'status_tiket' => 'verifikasi_admin',
            'catatan' => 'Tiket masuk',
            'created_at' => now()->subDay(),
            'updated_at' => now()->subDay(),
        ]);

        StatusTiket::create([
            'tiket_id' => $this->tiket->id,
            'status_tiket' => 'selesai',
            'catatan' => 'Selesai',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        TiketTeknisi::create([
            'tiket_id' => $this->tiket->id,
            'teknis_id' => $this->teknisi->id,
            'peran_teknisi' => 'teknisi_utama',
            'waktu_ditugaskan' => now()->subHours(8),
            'status_tugas' => 'selesai',
        ]);

        TiketBuktiFoto::create([
            'tiket_id' => $this->tiket->id,
            'foto_path' => 'bukti/foto.jpg',
        ]);
    }

    public function test_id_generator_and_prefixed_models_work(): void
    {
        $id = IdGenerator::make('TKT', true);

        $this->assertStringStartsWith('TKT-20260524-', $id);
        $this->assertLessThanOrEqual(36, strlen($id));
        $this->assertSame('USR-TIM', IdGenerator::userPrefix('tim_teknis'));
        $this->assertStringStartsWith('TKT-20260524-', $this->tiket->id);
        $this->assertStringStartsWith('USR-TIM-', $this->teknisiUser->id);
    }

    public function test_model_relations_and_ticket_helpers_execute_application_code(): void
    {
        $tiket = Tiket::with([
            'opd.user',
            'admin.user',
            'kategori',
            'kb.kategori',
            'sopInternal',
            'bidang',
            'latestStatus',
            'statusTiket',
            'tiketTeknisi.timTeknis.bidang',
            'buktiFoto',
        ])->findOrFail($this->tiket->id);

        $this->assertSame($this->opd->id, $tiket->opd->id);
        $this->assertSame($this->admin->id, $tiket->admin->id);
        $this->assertSame($this->kategoriSistem->id, $tiket->kategori_id);
        $this->assertSame($this->bidang->id, $tiket->bidang_id);
        $this->assertSame([$this->tiket->buktiFoto()->first()->foto_path], $tiket->getFotoPaths());
        $this->assertTrue($tiket->canBeReopened());
        $this->assertSame($tiket->created_at->toDateTimeString(), $tiket->getSlaPeriodStartDate()->toDateTimeString());
        $this->assertTrue($tiket->isCompletedOnTime(now(), 3));
        $this->assertFalse($tiket->isCompletedOnTime(now()->addDays(5), 3));

        $tiket->setRelation('chatRoom', null);
        $this->assertFalse($tiket->isTransferred());
        $this->assertNull($tiket->getTransferredFromAdmin());
    }

    public function test_chat_event_and_notifications_build_payloads(): void
    {
        Storage::fake('public');

        $room = ChatRoom::create([
            'tiket_id' => $this->tiket->id,
            'nama_roomchat' => 'teknis',
            'is_active' => true,
        ]);

        $message = ChatMessage::create([
            'room_id' => $room->id,
            'sender_id' => $this->adminUser->id,
            'konten' => 'Mohon dicek',
            'file_url' => 'chat/lampiran.pdf',
            'tipe_konten' => 'file',
            'created_at' => now(),
        ]);

        $event = new NewChatMessage($message->load('room'), 'Admin Helpdesk');

        $this->assertSame('NewChatMessage', $event->broadcastAs());
        $this->assertNotEmpty($event->broadcastOn());
        $this->assertSame('Admin Helpdesk', $event->broadcastWith()['sender_name']);

        $notifications = [
            new StatusTiketNotification($this->tiket->id, 'selesai', 'Tiket selesai', '/opd/tiket'),
            new TiketMasukNotification($this->tiket->id, 'Dinas Contoh', '/admin/tiket'),
            new TiketTransferNotification($this->tiket->id, 'Admin A', 'Admin B', '/admin/tiket'),
            new TugasBaruNotification($this->tiket->id, 'Internet lambat', '/tim/tiket'),
        ];

        foreach ($notifications as $notification) {
            $this->assertContains('database', $notification->via($this->opdUser));
            $this->assertIsArray($notification->toDatabase($this->opdUser));
            $this->assertIsArray($notification->toBroadcast($this->opdUser));
            $this->assertNotSame('', $notification->broadcastType());
        }

        $mail = $notifications[0]->toMail((object) ['name' => 'OPD']);
        $this->assertStringContainsString('SIPASTI', $mail->subject);
    }

    public function test_activity_log_static_methods_and_audit_queries(): void
    {
        $this->actingAs($this->superAdmin);

        ActivityLogController::logLogin($this->superAdmin);
        ActivityLogController::logLogout($this->superAdmin);
        ActivityLogController::logCreate($this->superAdmin->id, 'super_admin', 'users', $this->opdUser->id, ['email' => $this->opdUser->email]);
        ActivityLogController::logUpdate($this->superAdmin->id, 'super_admin', 'users', $this->opdUser->id, ['email' => 'old@test'], ['email' => 'new@test']);
        ActivityLogController::logDelete($this->superAdmin->id, 'super_admin', 'users', $this->opdUser->id, ['email' => $this->opdUser->email]);
        ActivityLogController::logEscalate($this->adminUser->id, 'admin_helpdesk', $this->tiket->id);
        ActivityLogController::logApprove($this->adminUser->id, 'admin_helpdesk', 'tiket', $this->tiket->id);
        ActivityLogController::logReject($this->adminUser->id, 'admin_helpdesk', 'tiket', $this->tiket->id);

        $controller = new ActivityLogController();

        $this->assertNotNull($controller->showAudit(Request::create('/audit', 'GET', [
            'search' => 'users',
            'role' => 'super_admin',
            'jenis' => 'create',
            'date_from' => now()->subDay()->toDateString(),
            'date_to' => now()->toDateString(),
        ])));

        $this->actingAs($this->adminUser);
        $this->assertNotNull($controller->showAdminLog(Request::create('/log', 'GET', ['jenis' => 'create'])));

        $this->actingAs($this->pimpinanUser);
        $this->assertNotNull($controller->showPimpinanLog(Request::create('/log', 'GET', ['role' => 'admin_helpdesk'])));

        foreach ([
            $controller->exportCsv(Request::create('/audit/export-csv', 'GET', ['jenis' => 'create'])),
            $controller->exportAdminCsv(Request::create('/admin/log/export-csv', 'GET', ['jenis' => 'create'])),
            $controller->exportPimpinanCsv(Request::create('/pimpinan/log/export-csv', 'GET', ['role' => 'admin_helpdesk'])),
        ] as $response) {
            ob_start();
            $response->sendContent();
            $content = ob_get_clean();

            $this->assertNotSame('', $content);
        }
    }

    public function test_pimpinan_dashboard_index_executes_all_period_branches(): void
    {
        $controller = new PimpinanDashboardController();

        foreach (['daily', 'weekly', 'monthly', 'yearly', 'custom'] as $period) {
            $request = Request::create('/pimpinan/dashboard', 'GET', [
                'period' => $period,
                'date_from' => now()->subDays(10)->toDateString(),
                'date_to' => now()->toDateString(),
            ]);

            $response = $controller->index($request);

            $this->assertSame('pimpinan.dashboard', $response->name());
        }
    }

    public function test_super_admin_user_management_success_paths_execute_real_controller(): void
    {
        $this->actingAs($this->superAdmin);
        Auth::login($this->superAdmin);

        $controller = new ManajemenPenggunaController();

        $this->assertSame('super_admin.manajemen-pengguna.internal', $controller->indexInternal(Request::create('/internal'))->name());

        $controller->storeTimTeknis($this->postRequest([
            'nama_lengkap' => 'Teknisi Baru',
            'email' => 'teknisi-baru@example.test',
            'password' => 'secret1',
            'bidang_id' => $this->bidang->id,
        ]));

        $newTeknisi = TimTeknis::where('nama_lengkap', 'Teknisi Baru')->firstOrFail();
        $controller->updateTimTeknis($this->putRequest([
            'nama_lengkap' => 'Teknisi Baru Update',
            'email' => 'teknisi-baru-update@example.test',
            'password' => 'secret2',
            'bidang_id' => $this->bidang->id,
        ]), $newTeknisi->id);
        $controller->destroyTimTeknis($newTeknisi->id);

        $controller->storeAdminHelpdesk($this->postRequest([
            'nama_lengkap' => 'Admin Baru',
            'email' => 'admin-baru@example.test',
            'password' => 'secret1',
            'bidang_id' => $this->bidang->id,
        ]));

        $newAdmin = AdminHelpdesk::where('nama_lengkap', 'Admin Baru')->firstOrFail();
        $controller->updateAdminHelpdesk($this->putRequest([
            'nama_lengkap' => 'Admin Baru Update',
            'email' => 'admin-baru-update@example.test',
            'password' => 'secret2',
            'bidang_id' => $this->bidang->id,
        ]), $newAdmin->id);
        $controller->destroyAdminHelpdesk($newAdmin->id);

        $controller->storePimpinan($this->postRequest([
            'nama_lengkap' => 'Pimpinan Baru',
            'email' => 'pimpinan-baru@example.test',
            'password' => 'secret1',
        ]));

        $newPimpinan = Pimpinan::where('nama_lengkap', 'Pimpinan Baru')->firstOrFail();
        $controller->updatePimpinan($this->putRequest([
            'nama_lengkap' => 'Pimpinan Baru Update',
            'email' => 'pimpinan-baru-update@example.test',
            'password' => 'secret2',
        ]), $newPimpinan->id);
        $controller->destroyPimpinan($newPimpinan->id);

        $this->assertDatabaseHas('activity_log', ['jenis_aktivitas' => 'delete']);
    }

    public function test_admin_helpdesk_ticket_management_success_paths_execute_real_controller(): void
    {
        Notification::fake();
        $this->actingAs($this->adminUser);
        Auth::login($this->adminUser);

        $controller = new AdminManajemenTiketController();

        $waitingTicket = $this->freshTicket('Tiket menunggu', null, 'verifikasi_admin');
        $this->assertSame('admin_helpdesk.manajemen-tiket.menunggu-verif', $controller->menungguVerif(Request::create('/admin-helpdesk/tiket/menunggu-verif', 'GET', [
            'search' => 'Tiket',
            'opd_id' => $this->opd->id,
            'rekomendasi_penanganan' => 'eskalasi',
        ]))->name());

        $controller->terimaProses($this->postRequest([]), $waitingTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $waitingTicket->id,
            'status_tiket' => 'panduan_remote',
        ]);

        $revisionTicket = $this->freshTicket('Tiket revisi', null, 'verifikasi_admin');
        $controller->revisi($this->postRequest(['alasan_revisi' => 'Lengkapi kronologi']), $revisionTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $revisionTicket->id,
            'status_tiket' => 'perlu_revisi',
        ]);

        $transferTicket = $this->freshTicket('Tiket transfer', $this->admin->id, 'panduan_remote');
        ChatRoom::create([
            'tiket_id' => $transferTicket->id,
            'nama_roomchat' => 'admin',
            'is_active' => true,
        ])->users()->syncWithoutDetaching([
            $this->adminUser->id => [
                'bidang_id' => $this->bidang->id,
                'sequence_number' => 1,
                'started_at' => now(),
                'is_active' => true,
            ],
        ]);
        $controller->transfer($this->postRequest([
            'bidang_id' => $this->bidang->id,
            'instruksi' => 'Cek ulang bidang',
        ]), $transferTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $transferTicket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);

        $escalationTicket = $this->freshTicket('Tiket eskalasi', $this->admin->id, 'panduan_remote');
        ChatRoom::create([
            'tiket_id' => $escalationTicket->id,
            'nama_roomchat' => 'admin',
            'is_active' => true,
        ]);
        $controller->eskalasi($this->postRequest([
            'teknisi_utama_id' => $this->teknisi->id,
            'instruksi' => 'Datangi lokasi',
        ]), $escalationTicket->id);
        $this->assertDatabaseHas('tiket_teknisi', [
            'tiket_id' => $escalationTicket->id,
            'teknis_id' => $this->teknisi->id,
            'status_tugas' => 'aktif',
        ]);

        $doneByAdminTicket = $this->freshTicket('Tiket selesai admin', $this->admin->id, 'panduan_remote', '[Dibuka Kembali oleh OPD] Butuh cek ulang');
        $doneByAdminTicket->update(['reopened_count' => 3]);
        $controller->selesaiOlehAdmin($this->postRequest(['catatan' => 'Sudah selesai remote']), $doneByAdminTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $doneByAdminTicket->id,
            'status_tiket' => 'tiket_ditutup',
        ]);

        foreach ([
            [$controller->panduan(Request::create('/panduan', 'GET', ['search' => 'Tiket'])), 'admin_helpdesk.manajemen-tiket.panduan-remote'],
            [$controller->distribusi(Request::create('/distribusi', 'GET', ['kategori_id' => $this->kategoriSistem->id])), 'admin_helpdesk.manajemen-tiket.distribusi'],
            [$controller->riwayat(Request::create('/riwayat', 'GET', ['rekomendasi_penanganan' => 'eskalasi'])), 'admin_helpdesk.manajemen-tiket.riwayat'],
        ] as [$view, $name]) {
            $this->assertSame($name, $view->name());
        }

        $stream = $controller->exportCsv(Request::create('/riwayat/export-csv', 'GET', ['search' => 'Tiket']));
        $this->assertSame(200, $stream->getStatusCode());
    }

    public function test_super_admin_knowledge_base_controller_success_paths_execute_real_code(): void
    {
        Storage::fake('public');
        $this->actingAs($this->superAdmin);
        Auth::login($this->superAdmin);

        $controller = new KnowledgeBaseController();

        $this->assertSame('super_admin.pustaka.index', $controller->indexOpd()->name());
        $this->assertSame('super_admin.pustaka.opd-kategori', $controller->opdKategori(Request::create('/kb', 'GET', [
            'search' => 'Reset',
            'status' => 'published',
        ]), $this->kategoriArtikel->id)->name());
        $this->assertSame('super_admin.pustaka.index', $controller->indexInternal()->name());
        $this->assertSame('super_admin.pustaka.internal-bidang', $controller->internalBidang(Request::create('/kb', 'GET', [
            'search' => 'SOP',
            'status' => 'published',
        ]), $this->bidang->id)->name());
        $this->assertSame('super_admin.pustaka.form', $controller->create(Request::create('/kb/create', 'GET', [
            'visibility' => 'opd',
            'kategori_id' => $this->kategoriArtikel->id,
        ]))->name());

        $controller->storeKategori($this->postRequest([
            'nama_kategori' => 'FAQ Baru',
            'deskripsi' => 'Kategori baru',
        ]));
        $newKategori = KategoriArtikel::where('nama_kategori', 'FAQ Baru')->firstOrFail();
        $controller->updateKategori($this->putRequest([
            'nama_kategori' => 'FAQ Update',
            'deskripsi' => 'Kategori update',
        ]), $newKategori->id);
        $controller->destroyKategori($newKategori->id);

        $controller->storeBidang($this->postRequest([
            'nama_bidang' => 'Aplikasi Baru',
            'batas_hari_pengerjaan' => 5,
        ]));
        $newBidang = Bidang::where('nama_bidang', 'Aplikasi Baru')->firstOrFail();
        $controller->updateBidang($this->putRequest([
            'nama_bidang' => 'Aplikasi Update',
            'batas_hari_pengerjaan' => 6,
        ]), $newBidang->id);
        $controller->destroyBidang($newBidang->id);

        $controller->store($this->postRequest([
            'nama_artikel_sop' => 'Artikel Aman',
            'isi_konten' => '<h1 onclick="bad()">Judul</h1><script>alert(1)</script><p>Isi</p>',
            'deskripsi_singkat' => 'Deskripsi',
            'status_publikasi' => 'published',
            'visibilitas_akses' => 'opd',
            'kategori_artikel_id' => $this->kategoriArtikel->id,
        ]));
        $newArticle = ArtikelOpd::where('judul', 'Artikel Aman')->firstOrFail();
        $lampiran = LampiranArtikel::create([
            'artikel_opd_id' => $newArticle->id,
            'nama_file' => 'manual.pdf',
            'path_file' => 'knowledge_base/manual.pdf',
            'tipe_file' => 'pdf',
            'ukuran_file' => 123,
            'urutan' => 1,
        ]);

        $this->assertSame('super_admin.pustaka.form', $controller->edit($newArticle->id)->name());
        $this->assertSame('super_admin.pustaka.preview', $controller->preview($newArticle->id)->name());
        $controller->update($this->putRequest([
            'nama_artikel_sop' => 'Artikel Aman Update',
            'isi_konten' => '<p>Konten update</p>',
            'deskripsi_singkat' => 'Update',
            'status_publikasi' => 'draft',
            'visibilitas_akses' => 'opd',
            'kategori_artikel_id' => $this->kategoriArtikel->id,
            'remove_lampiran_ids' => [$lampiran->id],
        ]), $newArticle->id);

        $imageRequest = $this->filePostRequest([
            'image' => UploadedFile::fake()->image('inline.jpg', 20, 20),
        ]);
        $uploadResponse = $controller->uploadImage($imageRequest);
        $this->assertSame(200, $uploadResponse->getStatusCode());

        $controller->destroy($newArticle->id);
        $this->assertDatabaseMissing('artikel_opd', ['id' => $newArticle->id]);
    }

    public function test_super_admin_konfigurasi_sistem_controller_success_paths_execute_real_code(): void
    {
        $controller = new KonfigurasiSistemController();

        $this->assertSame('super_admin.konfigurasiSistem', $controller->index()->name());

        $storeKategoriResponse = $controller->storeKategori($this->postRequest([
            'nama_kategori' => 'Email',
            'deskripsi' => 'Masalah email',
            'icon' => 'default',
        ]));
        $kategoriId = $storeKategoriResponse->getData(true)['id'];

        $controller->updateKategori($this->putRequest([
            'nama_kategori' => 'Email Update',
            'deskripsi' => 'Update',
            'icon' => 'default',
        ]), $kategoriId);

        $questionResponse = $controller->storeNode($this->postRequest([
            'kategori_id' => $kategoriId,
            'tipe_node' => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah email bisa login?',
            'hint_konteks' => 'Cek akses',
            'routing_ya_type' => 'solusi',
            'routing_tidak_type' => 'pertanyaan',
        ]));
        $questionData = $questionResponse->getData(true);
        $questionId = $questionData['node']['id'];

        $controller->updateNode($this->putRequest([
            'tipe_node' => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah email masih bermasalah?',
            'hint_konteks' => 'Cek ulang',
            'routing_ya_type' => 'solusi',
            'routing_tidak_type' => null,
        ]), $questionId);

        $solutionResponse = $controller->storeNode($this->postRequest([
            'kategori_id' => $kategoriId,
            'tipe_node' => 'solusi',
            'judul_solusi' => 'Reset email',
            'penjelasan_solusi' => 'Reset password email',
            'rekomendasi_penanganan' => 'admin',
            'bidang_id' => $this->bidang->id,
            'kb_id' => $this->artikel->id,
            'sop_internal_id' => $this->sop->id,
        ]));
        $solutionId = $solutionResponse->getData(true)['node']['id'];

        $controller->updateNode($this->putRequest([
            'tipe_node' => 'solusi',
            'judul_solusi' => 'Reset email update',
            'penjelasan_solusi' => 'Reset lewat admin',
            'rekomendasi_penanganan' => 'eskalasi',
            'bidang_id' => $this->bidang->id,
            'kb_id' => $this->artikel->id,
            'sop_internal_id' => $this->sop->id,
        ]), $solutionId);

        $controller->destroyNode($solutionId);
        $controller->destroyKategori($kategoriId);

        $this->assertDatabaseMissing('kategori_sistem', ['id' => $kategoriId]);
    }

    public function test_tim_teknis_antrean_controller_success_paths_execute_real_code(): void
    {
        Notification::fake();
        $this->actingAs($this->teknisiUser);
        Auth::login($this->teknisiUser);

        $controller = new TimTeknisAntreanController();

        $activeTicket = $this->freshTicket('Tugas aktif teknisi', $this->admin->id, 'perbaikan_teknis');
        $this->assignTechnician($activeTicket, 'aktif');
        $room = ChatRoom::create([
            'tiket_id' => $activeTicket->id,
            'nama_roomchat' => 'teknis',
            'is_active' => true,
        ]);
        ChatMessage::create([
            'room_id' => $room->id,
            'sender_id' => $this->adminUser->id,
            'konten' => 'Ada pesan baru',
            'tipe_konten' => 'text',
            'created_at' => now(),
        ]);

        $this->assertSame('tim_teknis.antrean', $controller->index(Request::create('/tim-teknis/antrean', 'GET', [
            'search' => 'Tugas',
            'peran' => 'teknisi_utama',
        ]))->name());

        $doneTicket = $this->freshTicket('Tugas selesai teknisi', $this->admin->id, 'dibuka_kembali', 'Buka kembali');
        $doneTicket->update(['reopened_count' => 3]);
        $this->assignTechnician($doneTicket, 'selesai');
        $controller->selesai($this->postRequest(['catatan' => 'Sudah diperbaiki']), $doneTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $doneTicket->id,
            'status_tiket' => 'tiket_ditutup',
        ]);

        $failedTicket = $this->freshTicket('Tugas gagal teknisi', $this->admin->id, 'perbaikan_teknis');
        $this->assignTechnician($failedTicket, 'aktif');
        $controller->gagal($this->postRequest([
            'analisis_kerusakan' => 'Mainboard rusak',
            'spesifikasi_perangkat_rusak' => 'Router X',
            'rekomendasi' => 'Pengadaan baru',
        ]), $failedTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $failedTicket->id,
            'status_tiket' => 'rusak_berat',
        ]);

        $returnedTicket = $this->freshTicket('Tugas salah bidang', $this->admin->id, 'perbaikan_teknis');
        $this->assignTechnician($returnedTicket, 'aktif');
        $controller->kembalikan($this->postRequest(['alasan_kembalikan' => 'Bukan kewenangan bidang']), $returnedTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $returnedTicket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);

        $this->assertSame('tim_teknis.riwayat', $controller->riwayat(Request::create('/tim-teknis/riwayat', 'GET', [
            'search' => 'Tugas',
            'peran' => 'teknisi_utama',
        ]))->name());
    }

    public function test_opd_pengaduan_and_diagnosis_success_paths_execute_real_code(): void
    {
        Storage::fake('public');
        Notification::fake();
        $this->actingAs($this->opdUser);
        Auth::login($this->opdUser);

        $question = NodeDiagnosis::create([
            'kategori_id' => $this->kategoriSistem->id,
            'tipe_node' => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah jaringan mati?',
            'id_next_ya' => $this->node->id,
        ]);

        $diagnosis = new DiagnosisMandiriController();

        $this->assertSame('opd.buat-pengaduan.index', $diagnosis->index()->name());
        $this->assertTrue($diagnosis->mulai($this->kategoriSistem->id)->isRedirect());
        $this->assertSame('opd.buat-pengaduan.node', $diagnosis->showNode($question->id, Request::create('/node', 'GET', [
            'kategori_id' => $this->kategoriSistem->id,
            'kategori_nama' => $this->kategoriSistem->nama_kategori,
            'diagnosa' => 'Awal',
            'q' => 1,
        ]))->name());
        $this->assertSame('opd.buat-pengaduan.solusi', $diagnosis->showNode($this->node->id, Request::create('/node', 'GET', [
            'kategori_id' => $this->kategoriSistem->id,
            'kategori_nama' => $this->kategoriSistem->nama_kategori,
        ]))->name());
        $this->assertSame('opd.buat-pengaduan.tiket', $diagnosis->showTiket(Request::create('/tiket', 'GET', [
            'kategori_id' => $this->kategoriSistem->id,
            'kategori_nama' => $this->kategoriSistem->nama_kategori,
            'node_diagnosis_id' => $this->node->id,
            'rekomendasi_penanganan' => 'eskalasi',
        ]))->name());

        $duplicateResponse = $diagnosis->storeTiket($this->postRequest([
            'subjek_masalah' => 'Internet lambat',
            'detail_masalah' => 'Koneksi sangat lambat sejak pagi',
            'node_diagnosis_id' => $this->node->id,
            'rekomendasi_penanganan' => 'eskalasi',
            'spesifikasi_perangkat' => 'Router utama',
            'lokasi' => 'Gedung A',
        ]));
        $this->assertTrue($duplicateResponse->isRedirect());

        $diagnosis->storeTiket($this->filePostRequest([
            'foto_bukti' => [UploadedFile::fake()->image('bukti.jpg', 20, 20)],
        ], [
            'subjek_masalah' => 'Printer lantai dua tidak mencetak',
            'detail_masalah' => 'Printer tidak merespons perintah cetak',
            'node_diagnosis_id' => $this->node->id,
            'rekomendasi_penanganan' => 'admin',
            'spesifikasi_perangkat' => 'Printer',
            'lokasi' => 'Lantai 2',
        ]));
        $createdTicket = Tiket::where('subjek_masalah', 'Printer lantai dua tidak mencetak')->firstOrFail();
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $createdTicket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);

        $pengaduan = new PengaduanSayaController();
        ChatRoom::create([
            'tiket_id' => $this->tiket->id,
            'nama_roomchat' => 'admin',
            'is_active' => true,
        ]);

        $this->assertSame('opd.pengaduan-saya.index', $pengaduan->index(Request::create('/opd/pengaduan-saya', 'GET', [
            'status' => 'selesai',
            'search' => 'Internet',
        ]))->name());
        $this->assertSame('opd.pengaduan-saya.detail', $pengaduan->show($this->tiket->id)->name());
        $this->assertSame('opd.pengaduan-saya.chat', $pengaduan->chat($this->tiket->id)->name());

        $pengaduan->konfirm($this->postRequest(['penilaian' => 4]), $this->tiket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $this->tiket->id,
            'status_tiket' => 'tiket_ditutup',
        ]);

        $adminResolvedTicket = $this->freshTicket('Buka kembali admin', $this->admin->id, 'selesai', '[Diselesaikan oleh Admin Helpdesk] Remote selesai');
        $pengaduan->bukaKembali($this->filePostRequest([
            'file_bukti' => UploadedFile::fake()->image('ulang.jpg', 20, 20),
        ], [
            'alasan' => 'Masalah muncul lagi',
        ]), $adminResolvedTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $adminResolvedTicket->id,
            'status_tiket' => 'panduan_remote',
        ]);

        $teknisResolvedTicket = $this->freshTicket('Buka kembali teknis', $this->admin->id, 'selesai', 'Tiket berhasil diperbaiki oleh tim teknis.');
        $this->assignTechnician($teknisResolvedTicket, 'selesai');
        $pengaduan->bukaKembali($this->postRequest(['alasan' => 'Kendala kembali terjadi']), $teknisResolvedTicket->id);
        $this->assertDatabaseHas('tiket_teknisi', [
            'tiket_id' => $teknisResolvedTicket->id,
            'teknis_id' => $this->teknisi->id,
            'status_tugas' => 'aktif',
        ]);

        $revisionTicket = $this->freshTicket('Butuh revisi', null, 'perlu_revisi', 'Mohon revisi');
        TiketBuktiFoto::create([
            'tiket_id' => $revisionTicket->id,
            'foto_path' => 'old/foto.jpg',
        ]);
        $this->assertSame('opd.pengaduan-saya.edit', $pengaduan->edit($revisionTicket->id)->name());
        $pengaduan->update($this->filePostRequest([
            'foto_bukti' => [UploadedFile::fake()->image('baru.jpg', 20, 20)],
        ], [
            'subjek_masalah' => 'Butuh revisi update',
            'detail_masalah' => 'Detail sudah dilengkapi',
            'spesifikasi_perangkat' => 'Laptop',
            'lokasi' => 'Ruang 1',
        ]), $revisionTicket->id);
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id' => $revisionTicket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);
    }

    public function test_chat_controllers_dashboards_and_pimpinan_export_execute_real_code(): void
    {
        Storage::fake('public');

        $this->actingAs($this->adminUser);
        Auth::login($this->adminUser);

        $adminTicket = $this->freshTicket('Chat admin aktif', $this->admin->id, 'panduan_remote');
        $adminRoom = ChatRoom::create([
            'tiket_id' => $adminTicket->id,
            'nama_roomchat' => 'admin',
            'is_active' => true,
        ]);
        ChatMessage::create([
            'room_id' => $adminRoom->id,
            'sender_id' => $this->opdUser->id,
            'konten' => 'Pesan OPD',
            'file_url' => 'chat/file.jpg',
            'tipe_konten' => 'image',
            'created_at' => now(),
        ]);

        $adminChat = new AdminChatController();
        $this->assertSame('admin_helpdesk.chat', $adminChat->show($adminTicket->id)->name());
        $adminSendResponse = $adminChat->send($this->filePostRequest([
            'file' => UploadedFile::fake()->image('admin.jpg', 20, 20),
        ], [
            'konten' => 'Baik, kami cek',
        ]), $adminTicket->id);
        $this->assertSame(200, $adminSendResponse->getStatusCode());
        $this->assertSame('admin_helpdesk.dashboard', (new AdminDashboardController())->index()->name());

        $this->actingAs($this->teknisiUser);
        Auth::login($this->teknisiUser);

        $technicalTicket = $this->freshTicket('Chat teknis aktif', $this->admin->id, 'perbaikan_teknis');
        $this->assignTechnician($technicalTicket, 'aktif');
        ChatRoom::create([
            'tiket_id' => $technicalTicket->id,
            'nama_roomchat' => 'admin',
            'is_active' => false,
        ]);
        $technicalRoom = ChatRoom::create([
            'tiket_id' => $technicalTicket->id,
            'nama_roomchat' => 'teknis',
            'is_active' => true,
        ]);
        ChatMessage::create([
            'room_id' => $technicalRoom->id,
            'sender_id' => $this->opdUser->id,
            'konten' => 'Pesan teknis',
            'tipe_konten' => 'text',
            'created_at' => now(),
        ]);

        $technicalChat = new TimTeknisChatController();
        $this->assertSame('tim_teknis.chat', $technicalChat->show($technicalTicket->id)->name());
        $technicalSendResponse = $technicalChat->send($this->filePostRequest([
            'file' => UploadedFile::fake()->image('teknis.png', 20, 20),
        ], [
            'konten' => 'Foto kondisi perangkat',
        ]), $technicalTicket->id);
        $this->assertSame(200, $technicalSendResponse->getStatusCode());
        $this->assertSame('tim_teknis.dashboard', (new TimTeknisDashboardController())->index()->name());

        $this->actingAs($this->pimpinanUser);
        Auth::login($this->pimpinanUser);

        $xlsxResponse = (new PimpinanDashboardController())->exportXlsx(Request::create('/pimpinan/export-xlsx', 'GET', [
            'date_from' => now()->subDays(3)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $this->assertSame(200, $xlsxResponse->getStatusCode());

        $csvNamedXlsxResponse = (new PimpinanDashboardController())->exportCsv(Request::create('/pimpinan/export-csv', 'GET', [
            'date_from' => now()->subDays(3)->toDateString(),
            'date_to' => now()->toDateString(),
        ]));
        $this->assertSame(200, $csvNamedXlsxResponse->getStatusCode());
    }

    public function test_opd_help_center_and_chat_controller_success_paths_execute_real_code(): void
    {
        Storage::fake('public');
        $this->actingAs($this->opdUser);
        Auth::login($this->opdUser);

        ArtikelOpd::create([
            'kategori_artikel_id' => $this->kategoriArtikel->id,
            'judul' => 'Artikel Terkait',
            'deskripsi_singkat' => 'Artikel terkait',
            'isi_konten' => '<h2>Terkait</h2><p>Konten</p>',
            'status_publikasi' => 'published',
            'total_views' => 2,
        ]);

        $bantuan = new BantuanController();
        $this->assertSame('opd.bantuan.index', $bantuan->index(Request::create('/opd/bantuan', 'GET', [
            'search' => 'Reset',
        ]))->name());
        $this->assertSame('opd.bantuan.kategori', $bantuan->kategori($this->kategoriArtikel->id, Request::create('/opd/bantuan/kategori', 'GET', [
            'search' => 'Reset',
        ]))->name());
        $this->assertSame('opd.bantuan.artikel', $bantuan->artikel($this->artikel->id)->name());
        $this->assertSame(200, $bantuan->rating($this->postRequest(['rating' => 5]), $this->artikel->id)->getStatusCode());
        $this->assertSame(200, $bantuan->rating($this->postRequest(['rating' => 3]), $this->artikel->id)->getStatusCode());

        $chatTicket = $this->freshTicket('Chat OPD admin', $this->admin->id, 'panduan_remote');
        ChatRoom::create([
            'tiket_id' => $chatTicket->id,
            'nama_roomchat' => 'admin',
            'is_active' => true,
        ]);

        $opdChat = new OpdChatController();
        $this->assertSame('opd.pengaduan-saya.chat', $opdChat->show($chatTicket->id, Request::create('/chat', 'GET', [
            'type' => 'admin',
        ]))->name());
        $adminResponse = $opdChat->send($this->filePostRequest([
            'file' => UploadedFile::fake()->image('opd-admin.jpg', 20, 20),
        ], [
            'type' => 'admin',
            'konten' => 'Mohon bantuan',
        ]), $chatTicket->id);
        $this->assertSame(200, $adminResponse->getStatusCode());

        $technicalTicket = $this->freshTicket('Chat OPD teknis', $this->admin->id, 'perbaikan_teknis');
        ChatRoom::create([
            'tiket_id' => $technicalTicket->id,
            'nama_roomchat' => 'teknis',
            'is_active' => true,
        ]);
        $this->assertSame('opd.pengaduan-saya.chat', $opdChat->show($technicalTicket->id, Request::create('/chat', 'GET', [
            'type' => 'teknis',
        ]))->name());
        $technicalResponse = $opdChat->send($this->postRequest([
            'type' => 'teknis',
            'konten' => 'Pesan ke teknisi',
        ]), $technicalTicket->id);
        $this->assertSame(200, $technicalResponse->getStatusCode());
    }

    public function test_profile_notification_and_super_admin_dashboard_execute_real_code(): void
    {
        $this->actingAs($this->superAdmin);
        Auth::login($this->superAdmin);

        $this->assertSame('super_admin.dashboard', (new SuperAdminDashboardController())->index()->name());

        $profile = new UserProfileController();
        foreach ([
            [$this->opdUser, 'opd.profil'],
            [$this->adminUser, 'admin_helpdesk.profil'],
            [$this->teknisiUser, 'tim_teknis.profil'],
            [$this->pimpinanUser, 'pimpinan.profil'],
        ] as [$user, $view]) {
            $this->assertSame($view, $profile->show($this->requestForUser($user))->name());
        }

        $wrongPasswordResponse = $profile->updatePassword($this->requestForUser($this->opdUser, 'POST', [
            'password_lama' => 'salah',
            'password_baru' => 'password-baru',
            'password_baru_confirmation' => 'password-baru',
        ]));
        $this->assertTrue($wrongPasswordResponse->isRedirect());

        $samePasswordResponse = $profile->updatePassword($this->requestForUser($this->opdUser, 'POST', [
            'password_lama' => 'password',
            'password_baru' => 'password',
            'password_baru_confirmation' => 'password',
        ]));
        $this->assertTrue($samePasswordResponse->isRedirect());

        $mismatchPasswordResponse = $profile->updatePassword($this->requestForUser($this->opdUser, 'POST', [
            'password_lama' => 'password',
            'password_baru' => 'password-baru',
            'password_baru_confirmation' => 'beda',
        ]));
        $this->assertTrue($mismatchPasswordResponse->isRedirect());

        $successPasswordResponse = $profile->updatePassword($this->requestForUser($this->opdUser, 'POST', [
            'password_lama' => 'password',
            'password_baru' => 'password-baru',
            'password_baru_confirmation' => 'password-baru',
        ]));
        $this->assertTrue($successPasswordResponse->isRedirect());

        $notification = $this->opdUser->notifications()->create([
            'id' => IdGenerator::make('NTF'),
            'type' => 'UnitTestNotification',
            'data' => [
                'icon' => 'info',
                'title' => 'Judul',
                'body' => 'Isi notifikasi',
                'url' => '/unit',
            ],
        ]);

        $this->actingAs($this->opdUser);
        Auth::login($this->opdUser);

        $controller = new NotificationController();
        $jsonResponse = $controller->index(Request::create('/notif', 'GET', [], [], [], ['HTTP_ACCEPT' => 'application/json']));
        $this->assertSame(200, $jsonResponse->getStatusCode());
        $this->assertSame('notifikasi.index', $controller->index(Request::create('/notif'))->name());
        $this->assertSame(200, $controller->markRead($notification->id)->getStatusCode());
        $this->assertSame(200, $controller->markAllRead()->getStatusCode());
    }

    public function test_tim_teknis_pustaka_controller_real_queries_and_check_role_middleware(): void
    {
        $this->actingAs($this->teknisiUser);
        Auth::login($this->teknisiUser);

        SopInternal::create([
            'bidang_id' => $this->bidang->id,
            'judul' => 'Draft jaringan internal',
            'deskripsi_singkat' => 'Draft tidak tampil saat filter published',
            'isi_konten' => '<p>Draft</p>',
            'status_publikasi' => 'draft',
            'total_views' => 0,
        ]);

        $controller = new \App\Http\Controllers\TimTeknis\PustakaController();

        $index = $controller->index(Request::create('/tim-teknis/pustaka', 'GET', [
            'search' => 'SOP',
            'status' => 'published',
        ]));

        $this->assertSame('tim_teknis.pustaka', $index->name());
        $this->assertSame('tim_teknis.pustaka-show', $controller->show($this->sop->id)->name());

        $middleware = new CheckRole();
        $response = $middleware->handle(
            Request::create('/tim-teknis/pustaka'),
            fn () => new Response('ok', 200),
            'tim_teknis'
        );

        $this->assertSame(200, $response->getStatusCode());
        $this->assertSame('ok', $response->getContent());
    }

    public function test_requested_controller_coverage_targets_execute_real_code(): void
    {
        $this->actingAs($this->opdUser);
        Auth::login($this->opdUser);

        $opdDashboard = (new OpdDashboardController())->index();
        $this->assertSame('opd.dashboard', $opdDashboard->name());

        $this->actingAs($this->adminUser);
        Auth::login($this->adminUser);

        $adminPustaka = new AdminPustakaController();
        $this->assertSame('admin_helpdesk.pustaka.index', $adminPustaka->index(Request::create('/admin-helpdesk/pustaka', 'GET', [
            'search' => 'SOP',
            'status' => 'published',
        ]))->name());
        $this->assertSame('admin_helpdesk.pustaka.show', $adminPustaka->show($this->sop->id)->name());

        $this->actingAs($this->superAdmin);
        Auth::login($this->superAdmin);

        $userManagement = new ManajemenPenggunaController();
        $this->assertSame('super_admin.manajemen-pengguna.opd', $userManagement->indexOpd(Request::create('/super-admin/pengguna/opd'))->name());

        $parentOpd = Opd::create([
            'user_id' => $this->user('opd', 'parent-opd@example.test')->id,
            'kode_opd' => 'PARENT',
            'nama_opd' => 'Induk OPD',
            'is_bagian' => 'N',
        ]);

        $userManagement->storeOpd($this->postRequest([
            'kode_opd' => 'OPD-BARU',
            'nama_opd' => 'OPD Baru',
            'email' => 'opd-baru@example.test',
            'password' => 'secret1',
            'kdunit' => '99',
            'parent_id' => $parentOpd->id,
            'is_bagian' => 'Y',
        ]));
        $createdOpd = Opd::where('kode_opd', 'OPD-BARU')->firstOrFail();

        $userManagement->updateOpd($this->putRequest([
            'kode_opd' => 'OPD-UPDATE',
            'nama_opd' => 'OPD Update',
            'email' => 'opd-update@example.test',
            'password' => 'secret2',
            'kdunit' => '100',
            'parent_id' => $parentOpd->id,
            'is_bagian' => 'N',
        ]), $createdOpd->id);
        $this->assertDatabaseHas('opd', [
            'id' => $createdOpd->id,
            'kode_opd' => 'OPD-UPDATE',
        ]);

        $userManagement->destroyOpd($createdOpd->id);
        $this->assertDatabaseMissing('opd', ['id' => $createdOpd->id]);
    }

    public function test_profile_controller_http_paths_execute_update_and_destroy(): void
    {
        $profileUser = $this->user('opd', 'profile-user@example.test');
        Opd::create([
            'user_id' => $profileUser->id,
            'kode_opd' => 'PROFILE',
            'nama_opd' => 'Profile OPD',
        ]);

        $this->actingAs($profileUser)
            ->get('/profile')
            ->assertOk();

        $this->actingAs($profileUser)
            ->patch('/profile', [
                'name' => 'Profile User',
                'email' => 'profile-updated@example.test',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('users', [
            'id' => $profileUser->id,
            'email' => 'profile-updated@example.test',
        ]);
        $this->assertNull($profileUser->fresh()->email_verified_at);

        $deleteUser = $this->user('opd', 'delete-user@example.test');

        $this->actingAs($deleteUser)
            ->delete('/profile', [
                'password' => 'password',
            ])
            ->assertRedirect('/');

        $this->assertDatabaseMissing('users', ['id' => $deleteUser->id]);
    }

    private function user(string $role, string $email): User
    {
        return User::create([
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
        ]);
    }

    private function freshTicket(string $subject, ?string $adminId, string $status, ?string $catatan = null): Tiket
    {
        $ticket = Tiket::create([
            'opd_id' => $this->opd->id,
            'admin_id' => $adminId,
            'node_diagnosis_id' => $this->node->id,
            'rekomendasi_penanganan' => 'eskalasi',
            'subjek_masalah' => $subject,
            'detail_masalah' => $subject . ' detail',
            'lokasi' => 'Gedung A',
            'spesifikasi_perangkat' => 'Router',
            'created_at' => now()->subHours(2),
            'updated_at' => now()->subHours(2),
        ]);

        StatusTiket::create([
            'tiket_id' => $ticket->id,
            'status_tiket' => $status,
            'catatan' => $catatan,
            'created_at' => now()->subHour(),
            'updated_at' => now()->subHour(),
        ]);

        return $ticket;
    }

    private function assignTechnician(Tiket $ticket, string $status): void
    {
        TiketTeknisi::updateOrCreate([
            'tiket_id' => $ticket->id,
            'teknis_id' => $this->teknisi->id,
        ], [
            'peran_teknisi' => 'teknisi_utama',
            'waktu_ditugaskan' => now()->subHours(4),
            'status_tugas' => $status,
        ]);
    }

    private function postRequest(array $data): Request
    {
        $request = Request::create('/test', 'POST', $data);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    private function putRequest(array $data): Request
    {
        $request = Request::create('/test', 'PUT', $data);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    private function filePostRequest(array $files, array $data = []): Request
    {
        $request = Request::create('/test', 'POST', $data, [], $files);
        $request->setLaravelSession($this->app['session.store']);

        return $request;
    }

    private function requestForUser(User $user, string $method = 'GET', array $data = []): Request
    {
        $request = Request::create('/test', $method, $data);
        $request->setLaravelSession($this->app['session.store']);
        $request->setUserResolver(fn () => $user);

        return $request;
    }
}
