<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\KategoriArtikel;
use App\Models\KnowledgeBase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class KnowledgeBaseControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_pustaka_super_admin(): void
    {
        $this->get('/super-admin/pustaka/opd')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_pustaka_super_admin(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/super-admin/pustaka/opd')->assertForbidden();
    }

    // ─── Index OPD ────────────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_pustaka_opd(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/pustaka/opd');

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.index');
        $response->assertViewHas('tab', 'opd');
    }

    // ─── OPD Kategori ─────────────────────────────────────────────

    public function test_super_admin_dapat_melihat_artikel_per_kategori_opd(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriArtikel();
        $this->createKnowledgeBase(['kategori_artikel_id' => $kategori->id]);

        $response = $this->actingAs($user)
            ->get("/super-admin/pustaka/opd/kategori/{$kategori->id}");

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.opd-kategori');
    }

    public function test_opdKategori_mengembalikan_404_jika_kategori_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->get('/super-admin/pustaka/opd/kategori/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Index Internal ───────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_pustaka_internal(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/pustaka/internal');

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.index');
        $response->assertViewHas('tab', 'internal');
    }

    // ─── Internal Bidang ──────────────────────────────────────────

    public function test_super_admin_dapat_melihat_artikel_per_bidang_internal(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)
            ->get("/super-admin/pustaka/internal/bidang/{$bidang->id}");

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.internal-bidang');
    }

    // ─── Kategori CRUD ────────────────────────────────────────────

    public function test_super_admin_dapat_menambah_kategori_artikel(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka/kategori', [
                'nama_kategori' => 'Kategori Baru',
                'deskripsi'     => 'Deskripsi kategori',
            ]);

        $response->assertRedirect(route('super_admin.pustaka.opd'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('kategori_artikel', ['nama_kategori' => 'Kategori Baru']);
    }

    public function test_storeKategori_validasi_gagal_jika_nama_kosong(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka/kategori', [
                'nama_kategori' => '',
            ]);

        $response->assertSessionHasErrors('nama_kategori');
    }

    public function test_super_admin_dapat_memperbarui_kategori(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriArtikel(['nama_kategori' => 'Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pustaka/kategori/{$kategori->id}", [
                'nama_kategori' => 'Diperbarui',
            ]);

        $response->assertRedirect(route('super_admin.pustaka.opd'));
        $this->assertEquals('Diperbarui', $kategori->fresh()->nama_kategori);
    }

    public function test_super_admin_dapat_menghapus_kategori_tanpa_artikel(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriArtikel();

        $response = $this->actingAs($user)
            ->delete("/super-admin/pustaka/kategori/{$kategori->id}");

        $response->assertRedirect(route('super_admin.pustaka.opd'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_artikel', ['id' => $kategori->id]);
    }

    public function test_tidak_dapat_menghapus_kategori_yang_masih_punya_artikel(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriArtikel();
        $this->createKnowledgeBase(['kategori_artikel_id' => $kategori->id]);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pustaka/kategori/{$kategori->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_artikel', ['id' => $kategori->id]);
    }

    // ─── Bidang CRUD ──────────────────────────────────────────────

    public function test_super_admin_dapat_menambah_bidang(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka/bidang', [
                'nama_bidang' => 'Bidang Teknologi',
            ]);

        $response->assertRedirect(route('super_admin.pustaka.internal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('bidang', ['nama_bidang' => 'Bidang Teknologi']);
    }

    public function test_storeBidang_validasi_gagal_jika_nama_kosong(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka/bidang', [
                'nama_bidang' => '',
            ]);

        $response->assertSessionHasErrors('nama_bidang');
    }

    public function test_super_admin_dapat_memperbarui_bidang(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang(['nama_bidang' => 'Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pustaka/bidang/{$bidang->id}", [
                'nama_bidang' => 'Diperbarui',
            ]);

        $response->assertRedirect(route('super_admin.pustaka.internal'));
        $this->assertEquals('Diperbarui', $bidang->fresh()->nama_bidang);
    }

    public function test_super_admin_dapat_menghapus_bidang_tanpa_artikel_internal(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();

        $response = $this->actingAs($user)
            ->delete("/super-admin/pustaka/bidang/{$bidang->id}");

        $response->assertRedirect(route('super_admin.pustaka.internal'));
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('bidang', ['id' => $bidang->id]);
    }

    public function test_tidak_dapat_menghapus_bidang_yang_masih_punya_artikel(): void
    {
        $user   = $this->createUser('super_admin');
        $bidang = $this->createBidang();
        $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)
            ->delete("/super-admin/pustaka/bidang/{$bidang->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('bidang', ['id' => $bidang->id]);
    }

    // ─── Create Artikel ───────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_form_tambah_artikel(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/pustaka/tambah');

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.form');
    }

    // ─── Store Artikel ────────────────────────────────────────────

    public function test_super_admin_dapat_menambah_artikel_opd(): void
    {
        Storage::fake('public');
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriArtikel();

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka', [
                'nama_artikel_sop'    => 'Cara Mengatasi Masalah Jaringan',
                'isi_konten'          => '<p>Konten artikel</p>',
                'deskripsi_singkat'   => 'Deskripsi singkat',
                'status_publikasi'    => 'published',
                'visibilitas_akses'   => 'opd',
                'kategori_artikel_id' => $kategori->id,
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('knowledge_base', [
            'nama_artikel_sop'  => 'Cara Mengatasi Masalah Jaringan',
            'status_publikasi'  => 'published',
            'visibilitas_akses' => 'opd',
        ]);
    }

    public function test_store_validasi_gagal_jika_judul_kosong(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka', [
                'nama_artikel_sop'  => '',
                'status_publikasi'  => 'published',
                'visibilitas_akses' => 'opd',
            ]);

        $response->assertSessionHasErrors('nama_artikel_sop');
    }

    public function test_store_validasi_gagal_jika_status_publikasi_tidak_valid(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->post('/super-admin/pustaka', [
                'nama_artikel_sop'  => 'Test Artikel',
                'status_publikasi'  => 'tidak_valid',
                'visibilitas_akses' => 'opd',
            ]);

        $response->assertSessionHasErrors('status_publikasi');
    }

    // ─── Edit & Update Artikel ────────────────────────────────────

    public function test_super_admin_dapat_mengakses_form_edit_artikel(): void
    {
        Storage::fake('public');
        $user    = $this->createUser('super_admin');
        $artikel = $this->createKnowledgeBase();

        $response = $this->actingAs($user)->get("/super-admin/pustaka/{$artikel->id}/edit");

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.form');
        $response->assertViewHas('article');
    }

    public function test_super_admin_dapat_memperbarui_artikel(): void
    {
        Storage::fake('public');
        $user    = $this->createUser('super_admin');
        $artikel = $this->createKnowledgeBase(['nama_artikel_sop' => 'Judul Lama']);

        $response = $this->actingAs($user)
            ->put("/super-admin/pustaka/{$artikel->id}", [
                'nama_artikel_sop'  => 'Judul Diperbarui',
                'status_publikasi'  => 'published',
                'visibilitas_akses' => 'opd',
            ]);

        $response->assertRedirect();
        $response->assertSessionHas('success');
        $this->assertEquals('Judul Diperbarui', $artikel->fresh()->nama_artikel_sop);
    }

    // ─── Destroy Artikel ──────────────────────────────────────────

    public function test_super_admin_dapat_menghapus_artikel(): void
    {
        Storage::fake('public');
        $user    = $this->createUser('super_admin');
        $artikel = $this->createKnowledgeBase();

        $response = $this->actingAs($user)
            ->delete("/super-admin/pustaka/{$artikel->id}");

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $this->assertDatabaseMissing('knowledge_base', ['id' => $artikel->id]);
    }

    public function test_destroy_mengembalikan_404_jika_artikel_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->delete('/super-admin/pustaka/' . Str::uuid());

        $response->assertNotFound();
    }

    // ─── Preview ──────────────────────────────────────────────────

    public function test_super_admin_dapat_preview_artikel(): void
    {
        $user    = $this->createUser('super_admin');
        $artikel = $this->createKnowledgeBase();

        $response = $this->actingAs($user)
            ->get("/super-admin/pustaka/{$artikel->id}/preview");

        $response->assertOk();
        $response->assertViewIs('super_admin.pustaka.preview');
    }

    // ─── Upload Image ─────────────────────────────────────────────

    public function test_super_admin_dapat_upload_inline_image(): void
    {
        Storage::fake('public');
        $user = $this->createUser('super_admin');

        $file = UploadedFile::fake()->image('test.jpg', 300, 300);

        $response = $this->actingAs($user)
            ->postJson('/super-admin/pustaka/upload-image', [
                'image' => $file,
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['success', 'url']);
        $response->assertJsonPath('success', true);
    }

    public function test_uploadImage_validasi_gagal_jika_bukan_gambar(): void
    {
        Storage::fake('public');
        $user = $this->createUser('super_admin');

        $file = UploadedFile::fake()->create('document.pdf', 100, 'application/pdf');

        $response = $this->actingAs($user)
            ->postJson('/super-admin/pustaka/upload-image', [
                'image' => $file,
            ]);

        $response->assertUnprocessable();
    }
}
