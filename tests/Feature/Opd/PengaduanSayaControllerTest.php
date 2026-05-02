<?php

namespace Tests\Feature\Opd;

use App\Models\StatusTiket;
use App\Models\TiketTeknisi;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class PengaduanSayaControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_pengaduan_saya(): void
    {
        $this->get('/opd/pengaduan-saya')->assertRedirect('/login');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_pengaduan_saya(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->actingAs($user)->get('/opd/pengaduan-saya')->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_opd_dapat_melihat_daftar_pengaduan(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)->get('/opd/pengaduan-saya');

        $response->assertOk();
        $response->assertViewIs('opd.pengaduan-saya.index');
        $response->assertViewHas('tikets');
    }

    public function test_opd_mengembalikan_403_jika_tidak_punya_profil_opd(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/opd/pengaduan-saya');

        $response->assertForbidden();
    }

    public function test_index_mendukung_filter_status(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $response = $this->actingAs($user)
            ->get('/opd/pengaduan-saya?status=panduan_remote');

        $response->assertOk();
    }

    public function test_index_mendukung_pencarian_berdasarkan_subjek(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd, ['subjek_masalah' => 'Printer rusak']);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)
            ->get('/opd/pengaduan-saya?search=Printer');

        $response->assertOk();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_opd_dapat_melihat_detail_tiket_miliknya(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)
            ->get("/opd/pengaduan-saya/{$tiket->id}");

        $response->assertOk();
        $response->assertViewIs('opd.pengaduan-saya.detail');
        $response->assertViewHas('tiket');
    }

    public function test_opd_tidak_dapat_melihat_tiket_milik_opd_lain(): void
    {
        $user1  = $this->createUser('opd');
        $user2  = $this->createUser('opd');
        $opd2   = $this->createOpd($user2);
        $tiket  = $this->createTiket($opd2);

        $response = $this->actingAs($user1)
            ->get("/opd/pengaduan-saya/{$tiket->id}");

        $response->assertNotFound();
    }

    // ─── Konfirm ──────────────────────────────────────────────────

    public function test_opd_dapat_mengkonfirm_tiket_yang_selesai(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'selesai');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/konfirm", [
                'penilaian' => 5,
            ]);

        $response->assertRedirect(route('opd.tiket.index'));
        $response->assertSessionHas('success');
        $this->assertEquals(5, $tiket->fresh()->penilaian);
    }

    public function test_konfirm_membuat_status_tiket_ditutup(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'selesai');

        $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/konfirm", [
                'penilaian' => 4,
            ]);

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'tiket_ditutup',
        ]);
    }

    public function test_konfirm_gagal_jika_status_bukan_selesai_atau_rusak_berat(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/konfirm", [
                'penilaian' => 5,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_konfirm_validasi_gagal_jika_penilaian_kosong(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'selesai');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/konfirm", [
                'penilaian' => '',
            ]);

        $response->assertSessionHasErrors('penilaian');
    }

    public function test_konfirm_validasi_gagal_jika_penilaian_di_luar_rentang(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'selesai');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/konfirm", [
                'penilaian' => 6,
            ]);

        $response->assertSessionHasErrors('penilaian');
    }

    // ─── Edit ─────────────────────────────────────────────────────

    public function test_opd_dapat_mengakses_form_edit_saat_perlu_revisi(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perlu_revisi');

        $response = $this->actingAs($user)
            ->get("/opd/pengaduan-saya/{$tiket->id}/edit");

        $response->assertOk();
        $response->assertViewIs('opd.pengaduan-saya.edit');
    }

    public function test_edit_diarahkan_ke_detail_jika_status_bukan_perlu_revisi(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)
            ->get("/opd/pengaduan-saya/{$tiket->id}/edit");

        $response->assertRedirect(route('opd.tiket.show', $tiket->id));
        $response->assertSessionHas('error');
    }

    // ─── Update ───────────────────────────────────────────────────

    public function test_opd_dapat_memperbarui_tiket_saat_perlu_revisi(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perlu_revisi');

        $response = $this->actingAs($user)
            ->put("/opd/pengaduan-saya/{$tiket->id}", [
                'subjek_masalah' => 'Subjek diperbarui',
                'detail_masalah' => 'Detail masalah yang sudah diperbarui',
            ]);

        $response->assertRedirect(route('opd.tiket.show', $tiket->id));
        $response->assertSessionHas('success');

        $this->assertEquals('Subjek diperbarui', $tiket->fresh()->subjek_masalah);
    }

    public function test_update_membuat_status_verifikasi_admin_kembali(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perlu_revisi');

        $this->actingAs($user)
            ->put("/opd/pengaduan-saya/{$tiket->id}", [
                'subjek_masalah' => 'Subjek diperbarui',
                'detail_masalah' => 'Detail diperbarui',
            ]);

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);
    }

    public function test_update_gagal_jika_status_bukan_perlu_revisi(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $response = $this->actingAs($user)
            ->put("/opd/pengaduan-saya/{$tiket->id}", [
                'subjek_masalah' => 'Subjek diperbarui',
                'detail_masalah' => 'Detail diperbarui',
            ]);

        $response->assertRedirect(route('opd.tiket.show', $tiket->id));
        $response->assertSessionHas('error');
    }

    public function test_update_validasi_gagal_jika_subjek_kosong(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perlu_revisi');

        $response = $this->actingAs($user)
            ->put("/opd/pengaduan-saya/{$tiket->id}", [
                'subjek_masalah' => '',
                'detail_masalah' => 'Detail ada',
            ]);

        $response->assertSessionHasErrors('subjek_masalah');
    }

    // ─── Buka Kembali ─────────────────────────────────────────────

    public function test_opd_dapat_membuka_kembali_tiket_yang_selesai(): void
    {
        $bidang   = $this->createBidang();
        $user     = $this->createUser('opd');
        $opd      = $this->createOpd($user);
        $tiket    = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perbaikan_teknis');
        $this->createStatusTiket($tiket, 'selesai', [
            'catatan' => 'Telah diperbaiki oleh tim teknis.',
        ]);

        $ttUser  = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($ttUser, $bidang);
        $this->createTiketTeknisi($tiket, $teknisi, ['status_tugas' => 'selesai']);

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/buka-kembali", [
                'alasan' => 'Masalah masih belum selesai',
            ]);

        $response->assertRedirect(route('opd.tiket.show', $tiket->id));
        $response->assertSessionHas('success');
    }

    public function test_bukaKembali_gagal_jika_status_bukan_selesai(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/buka-kembali", [
                'alasan' => 'Masalah masih ada',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_bukaKembali_validasi_gagal_jika_alasan_kosong(): void
    {
        $user  = $this->createUser('opd');
        $opd   = $this->createOpd($user);
        $tiket = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'selesai');

        $response = $this->actingAs($user)
            ->post("/opd/pengaduan-saya/{$tiket->id}/buka-kembali", [
                'alasan' => '',
            ]);

        $response->assertSessionHasErrors('alasan');
    }
}
