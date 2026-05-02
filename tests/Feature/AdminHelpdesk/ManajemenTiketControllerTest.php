<?php

namespace Tests\Feature\AdminHelpdesk;

use App\Models\StatusTiket;
use App\Models\TiketTeknisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class ManajemenTiketControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    private function createAdminAndTiket(string $statusTiket = 'verifikasi_admin'): array
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('admin_helpdesk');
        $adminAh = $this->createAdminHelpdesk($user, $bidang);

        $opdUser  = $this->createUser('opd');
        $opd      = $this->createOpd($opdUser);
        $kategori = $this->createKategoriSistem(['bidang_id' => null]);

        $tiket = $this->createTiket($opd, [
            'admin_id'   => $adminAh->id,
            'bidang_id'  => $bidang->id,
            'kategori_id' => $kategori->id,
        ]);

        $this->createStatusTiket($tiket, $statusTiket);

        return compact('user', 'adminAh', 'bidang', 'tiket', 'opd', 'kategori');
    }

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_menunggu_verif(): void
    {
        $this->get('/admin-helpdesk/tiket/menunggu-verif')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_menunggu_verif(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/admin-helpdesk/tiket/menunggu-verif')->assertForbidden();
    }

    // ─── Menunggu Verif ───────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_halaman_menunggu_verif(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/tiket/menunggu-verif');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.manajemen-tiket.menunggu-verif');
    }

    public function test_menungguVerif_menampilkan_tiket_sesuai_bidang(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket('verifikasi_admin');

        $response = $this->actingAs($user)->get('/admin-helpdesk/tiket/menunggu-verif');

        $response->assertOk();
        $response->assertViewHas('tiketsVerif');
    }

    // ─── Terima Proses ────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_menerima_tiket(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('admin_helpdesk');
        $adminAh = $this->createAdminHelpdesk($user, $bidang);

        $opdUser  = $this->createUser('opd');
        $opd      = $this->createOpd($opdUser);
        $kategori = $this->createKategoriSistem(['nama_kategori' => 'Test Kategori']);

        \App\Models\KategoriSistem::where('id', $kategori->id)->update(['bidang_id' => $bidang->id]);

        $tiket = $this->createTiket($opd, ['bidang_id' => $bidang->id, 'kategori_id' => $kategori->id]);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/terima");

        $response->assertRedirect();
        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'panduan_remote',
        ]);
    }

    public function test_terimaProses_mengembalikan_404_jika_tiket_tidak_ada(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $fakeId = 'TKT-TIDAKADA';
        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$fakeId}/terima");

        $response->assertNotFound();
    }

    // ─── Revisi ───────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_meminta_revisi_tiket(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket();

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/revisi", [
                'alasan_revisi' => 'Foto bukti tidak jelas',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perlu_revisi',
        ]);
    }

    public function test_revisi_validasi_gagal_jika_alasan_kosong(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket();

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/revisi", [
                'alasan_revisi' => '',
            ]);

        $response->assertSessionHasErrors('alasan_revisi');
    }

    // ─── Transfer ─────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mentransfer_tiket_ke_bidang_lain(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket();

        $bidangTujuan = $this->createBidang();

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/transfer", [
                'bidang_id' => $bidangTujuan->id,
                'instruksi' => 'Tiket ini lebih sesuai ditangani bidang lain',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);

        $this->assertNull($tiket->fresh()->admin_id);
    }

    public function test_transfer_validasi_gagal_jika_bidang_id_tidak_ada(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket();

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/transfer", [
                'bidang_id' => Str::uuid(),
            ]);

        $response->assertSessionHasErrors('bidang_id');
    }

    // ─── Eskalasi ─────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengeskalasitiket_ke_tim_teknis(): void
    {
        ['user' => $user, 'tiket' => $tiket, 'bidang' => $bidang] = $this->createAdminAndTiket();

        $ttUser  = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($ttUser, $bidang);

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/eskalasi", [
                'teknisi_utama_id' => $teknisi->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'perbaikan_teknis',
        ]);

        $this->assertDatabaseHas('tiket_teknisi', [
            'tiket_id'      => $tiket->id,
            'teknis_id'     => $teknisi->id,
            'peran_teknisi' => 'teknisi_utama',
        ]);
    }

    public function test_eskalasi_dengan_teknisi_pendamping(): void
    {
        ['user' => $user, 'tiket' => $tiket, 'bidang' => $bidang] = $this->createAdminAndTiket();

        $ttUser1  = $this->createUser('tim_teknis');
        $teknisi1 = $this->createTimTeknis($ttUser1, $bidang);

        $ttUser2  = $this->createUser('tim_teknis');
        $teknisi2 = $this->createTimTeknis($ttUser2, $bidang);

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/eskalasi", [
                'teknisi_utama_id'       => $teknisi1->id,
                'teknisi_pendamping_ids' => [$teknisi2->id],
            ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('tiket_teknisi', [
            'tiket_id'      => $tiket->id,
            'teknis_id'     => $teknisi2->id,
            'peran_teknisi' => 'teknisi_pendamping',
        ]);
    }

    public function test_eskalasi_validasi_gagal_jika_teknisi_utama_tidak_ada(): void
    {
        ['user' => $user, 'tiket' => $tiket] = $this->createAdminAndTiket();

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/eskalasi", [
                'teknisi_utama_id' => Str::uuid(),
            ]);

        $response->assertSessionHasErrors('teknisi_utama_id');
    }

    // ─── Selesai Oleh Admin ───────────────────────────────────────

    public function test_admin_helpdesk_dapat_menyelesaikan_tiket_panduan_remote(): void
    {
        ['user' => $user, 'adminAh' => $adminAh] = $this->createAdminAndTiket('panduan_remote');
        $tiket = $adminAh->tiket()->first();

        if (!$tiket) {
            $opdUser = $this->createUser('opd');
            $opd     = $this->createOpd($opdUser);
            $tiket   = $this->createTiket($opd, ['admin_id' => $adminAh->id]);
            $this->createStatusTiket($tiket, 'panduan_remote');
        }

        $response = $this->actingAs($user)
            ->post("/admin-helpdesk/tiket/{$tiket->id}/selesai", [
                'catatan' => 'Panduan sudah diberikan, masalah teratasi',
            ]);

        $response->assertRedirect();
    }

    // ─── Panduan Remote ───────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_halaman_panduan_remote(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/tiket/panduan-remote');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.manajemen-tiket.panduan-remote');
    }

    // ─── Distribusi ───────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_halaman_distribusi(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/tiket/distribusi');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.manajemen-tiket.distribusi');
    }

    // ─── Riwayat ──────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_halaman_riwayat(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/tiket/riwayat');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.manajemen-tiket.riwayat');
    }

    // ─── Export CSV ───────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_export_tiket_menunggu_csv(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)
            ->get('/admin-helpdesk/tiket/menunggu-verif/export-csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }
}
