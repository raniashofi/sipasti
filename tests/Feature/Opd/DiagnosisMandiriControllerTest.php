<?php

namespace Tests\Feature\Opd;

use App\Models\NodeDiagnosis;
use App\Models\StatusTiket;
use App\Models\Tiket;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DiagnosisMandiriControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_diagnosis_mandiri(): void
    {
        $this->get('/opd/buat-pengaduan')->assertRedirect('/login');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_diagnosis_mandiri(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->actingAs($user)->get('/opd/buat-pengaduan')->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_opd_dapat_mengakses_halaman_pilih_kategori(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/buat-pengaduan');

        $response->assertOk();
        $response->assertViewIs('opd.buat-pengaduan.index');
        $response->assertViewHas('kategori');
    }

    // ─── Mulai Diagnosis ──────────────────────────────────────────

    public function test_mulai_diarahkan_ke_node_pertama_jika_ada_node(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $bidang   = $this->createBidang();
        $kategori = $this->createKategoriSistem();

        $nodeRoot = NodeDiagnosis::create([
            'id'              => (string) Str::uuid(),
            'kategori_id'     => $kategori->id,
            'tipe_node'       => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah komputer menyala?',
        ]);

        $response = $this->actingAs($user)
            ->get("/opd/buat-pengaduan/{$kategori->id}/mulai");

        $response->assertRedirect();
        $this->assertStringContainsString('node/' . $nodeRoot->id, $response->headers->get('Location'));
    }

    public function test_mulai_diarahkan_ke_form_tiket_jika_tidak_ada_node(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->get("/opd/buat-pengaduan/{$kategori->id}/mulai");

        $response->assertRedirect();
        $this->assertStringContainsString('tiket', $response->headers->get('Location'));
    }

    public function test_mulai_mengembalikan_404_jika_kategori_tidak_ditemukan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->get('/opd/buat-pengaduan/' . Str::uuid() . '/mulai');

        $response->assertNotFound();
    }

    // ─── Show Node ────────────────────────────────────────────────

    public function test_opd_dapat_melihat_node_pertanyaan(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriSistem();

        $node = NodeDiagnosis::create([
            'id'              => (string) Str::uuid(),
            'kategori_id'     => $kategori->id,
            'tipe_node'       => 'pertanyaan',
            'teks_pertanyaan' => 'Apakah komputer menyala?',
        ]);

        $response = $this->actingAs($user)
            ->get("/opd/buat-pengaduan/node/{$node->id}?kategori_id={$kategori->id}");

        $response->assertOk();
        $response->assertViewIs('opd.buat-pengaduan.node');
        $response->assertViewHas('node');
    }

    public function test_opd_dapat_melihat_node_solusi(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $bidang   = $this->createBidang();
        $kategori = $this->createKategoriSistem();
        $kb       = $this->createKnowledgeBase();

        $node = NodeDiagnosis::create([
            'id'           => (string) Str::uuid(),
            'kategori_id'  => $kategori->id,
            'tipe_node'    => 'solusi',
            'judul_solusi' => 'Restart komputer Anda',
            'kb_id'        => $kb->id,
            'bidang_id'    => $bidang->id,
        ]);

        $response = $this->actingAs($user)
            ->get("/opd/buat-pengaduan/node/{$node->id}?kategori_id={$kategori->id}");

        $response->assertOk();
        $response->assertViewIs('opd.buat-pengaduan.solusi');
    }

    public function test_showNode_mengembalikan_404_jika_node_tidak_ditemukan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->get('/opd/buat-pengaduan/node/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Show Form Tiket ──────────────────────────────────────────

    public function test_opd_dapat_mengakses_form_tiket(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/buat-pengaduan/tiket');

        $response->assertOk();
        $response->assertViewIs('opd.buat-pengaduan.tiket');
    }

    public function test_showTiket_meneruskan_parameter_yang_benar_ke_view(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->get("/opd/buat-pengaduan/tiket?kategori_id={$kategori->id}&rekomendasi_penanganan=admin");

        $response->assertOk();
        $response->assertViewHas('kategoriId', $kategori->id);
        $response->assertViewHas('rekomendasi', 'admin');
    }

    // ─── Store Tiket ──────────────────────────────────────────────

    public function test_opd_dapat_membuat_tiket_baru(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah' => 'Komputer tidak bisa menyala',
                'detail_masalah' => 'Saat dinyalakan tidak ada respon sama sekali',
            ]);

        $response->assertRedirect(route('opd.tiket.index'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('tiket', [
            'opd_id'         => $opd->id,
            'subjek_masalah' => 'Komputer tidak bisa menyala',
        ]);
    }

    public function test_storeTiket_membuat_status_verifikasi_admin_secara_otomatis(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah' => 'Masalah jaringan',
                'detail_masalah' => 'Tidak bisa terhubung ke internet',
            ]);

        $tiket = Tiket::first();
        $this->assertNotNull($tiket);

        $this->assertDatabaseHas('status_tiket', [
            'tiket_id'     => $tiket->id,
            'status_tiket' => 'verifikasi_admin',
        ]);
    }

    public function test_storeTiket_validasi_gagal_jika_subjek_masalah_kosong(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah' => '',
                'detail_masalah' => 'Detail masalah ada',
            ]);

        $response->assertSessionHasErrors('subjek_masalah');
    }

    public function test_storeTiket_validasi_gagal_jika_detail_masalah_kosong(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah' => 'Subjek ada',
                'detail_masalah' => '',
            ]);

        $response->assertSessionHasErrors('detail_masalah');
    }

    public function test_storeTiket_mengembalikan_403_jika_user_bukan_opd(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah' => 'Test',
                'detail_masalah' => 'Test',
            ]);

        $response->assertForbidden();
    }

    public function test_storeTiket_rekomendasi_tidak_valid_diset_null(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)
            ->post('/opd/buat-pengaduan/tiket', [
                'subjek_masalah'          => 'Test masalah',
                'detail_masalah'          => 'Detail test',
                'rekomendasi_penanganan'  => 'tidak_valid',
            ]);

        $tiket = Tiket::first();
        $this->assertNull($tiket->rekomendasi_penanganan);
    }
}
