<?php

namespace Tests\Feature\Opd;

use App\Models\KnowledgeBaseRating;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class BantuanControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_bantuan(): void
    {
        $this->get('/opd/bantuan')->assertRedirect('/login');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_bantuan_opd(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->actingAs($user)->get('/opd/bantuan')->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_opd_dapat_mengakses_halaman_bantuan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/bantuan');

        $response->assertOk();
        $response->assertViewIs('opd.bantuan.index');
        $response->assertViewHas('kategoris');
        $response->assertViewHas('topArtikel');
    }

    public function test_bantuan_index_mendukung_pencarian(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriArtikel();
        $this->createKnowledgeBase([
            'kategori_artikel_id' => $kategori->id,
            'nama_artikel_sop'    => 'Cara Reset Password',
        ]);

        $response = $this->actingAs($user)->get('/opd/bantuan?search=Reset');

        $response->assertOk();
        $response->assertViewHas('hasilCari');
    }

    public function test_bantuan_index_tanpa_pencarian_hasilCari_kosong(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/bantuan');

        $response->assertViewHas('hasilCari', collect());
    }

    // ─── Kategori ─────────────────────────────────────────────────

    public function test_opd_dapat_melihat_artikel_per_kategori(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriArtikel();
        $this->createKnowledgeBase(['kategori_artikel_id' => $kategori->id]);

        $response = $this->actingAs($user)->get("/opd/bantuan/kategori/{$kategori->id}");

        $response->assertOk();
        $response->assertViewIs('opd.bantuan.kategori');
        $response->assertViewHas('kategori');
        $response->assertViewHas('artikels');
    }

    public function test_kategori_mengembalikan_404_jika_tidak_ditemukan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->get('/opd/bantuan/kategori/' . Str::uuid());

        $response->assertNotFound();
    }

    public function test_kategori_mendukung_pencarian_artikel(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriArtikel();
        $this->createKnowledgeBase([
            'kategori_artikel_id' => $kategori->id,
            'nama_artikel_sop'    => 'Panduan Jaringan',
        ]);

        $response = $this->actingAs($user)
            ->get("/opd/bantuan/kategori/{$kategori->id}?search=Jaringan");

        $response->assertOk();
    }

    // ─── Artikel ──────────────────────────────────────────────────

    public function test_opd_dapat_melihat_detail_artikel(): void
    {
        $user     = $this->createUser('opd');
        $this->createOpd($user);
        $kategori = $this->createKategoriArtikel();
        $artikel  = $this->createKnowledgeBase(['kategori_artikel_id' => $kategori->id]);

        $response = $this->actingAs($user)->get("/opd/bantuan/artikel/{$artikel->id}");

        $response->assertOk();
        $response->assertViewIs('opd.bantuan.artikel');
        $response->assertViewHas('artikel');
    }

    public function test_melihat_artikel_menambah_total_views(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase(['total_views' => 5]);

        $this->actingAs($user)->get("/opd/bantuan/artikel/{$artikel->id}");

        $this->assertEquals(6, $artikel->fresh()->total_views);
    }

    public function test_artikel_mengembalikan_404_jika_status_draft(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase(['status_publikasi' => 'draft']);

        $response = $this->actingAs($user)->get("/opd/bantuan/artikel/{$artikel->id}");

        $response->assertNotFound();
    }

    public function test_artikel_mengembalikan_404_jika_tidak_ditemukan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)
            ->get('/opd/bantuan/artikel/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Rating ───────────────────────────────────────────────────

    public function test_opd_dapat_memberikan_rating_pada_artikel(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase([
            'rating'       => 0,
            'rating_count' => 0,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/opd/bantuan/artikel/{$artikel->id}/rating", [
                'rating' => 4,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['rating', 'rating_count', 'my_rating']);
        $response->assertJsonPath('my_rating', 4);

        $this->assertDatabaseHas('knowledge_base_rating', [
            'knowledge_base_id' => $artikel->id,
            'user_id'           => $user->id,
            'rating'            => 4,
        ]);
    }

    public function test_opd_dapat_memperbarui_rating_yang_sudah_ada(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase([
            'rating'       => 4,
            'rating_count' => 1,
        ]);

        KnowledgeBaseRating::create([
            'knowledge_base_id' => $artikel->id,
            'user_id'           => $user->id,
            'rating'            => 4,
        ]);

        $response = $this->actingAs($user)
            ->postJson("/opd/bantuan/artikel/{$artikel->id}/rating", [
                'rating' => 5,
            ]);

        $response->assertOk();
        $response->assertJsonPath('my_rating', 5);
    }

    public function test_rating_validasi_gagal_jika_nilai_di_luar_rentang(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase();

        $response = $this->actingAs($user)
            ->postJson("/opd/bantuan/artikel/{$artikel->id}/rating", [
                'rating' => 6,
            ]);

        $response->assertUnprocessable();
    }

    public function test_rating_validasi_gagal_jika_rating_tidak_diisi(): void
    {
        $user    = $this->createUser('opd');
        $this->createOpd($user);
        $artikel = $this->createKnowledgeBase();

        $response = $this->actingAs($user)
            ->postJson("/opd/bantuan/artikel/{$artikel->id}/rating", []);

        $response->assertUnprocessable();
        $response->assertJsonValidationErrors('rating');
    }

    public function test_tamu_tidak_dapat_memberikan_rating(): void
    {
        $artikel = $this->createKnowledgeBase();

        $response = $this->postJson("/opd/bantuan/artikel/{$artikel->id}/rating", [
            'rating' => 4,
        ]);

        $response->assertUnauthorized();
    }
}
