<?php

namespace Tests\Feature\SuperAdmin;

use App\Models\KategoriSistem;
use App\Models\NodeDiagnosis;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class KonfigurasiSistemControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_konfigurasi_sistem(): void
    {
        $this->get('/super-admin/konfigurasi/konfigurasi-sistem')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_konfigurasi_sistem(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)
            ->get('/super-admin/konfigurasi/konfigurasi-sistem')
            ->assertForbidden();
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_konfigurasi_sistem(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->actingAs($user)
            ->get('/super-admin/konfigurasi/konfigurasi-sistem')
            ->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_konfigurasi_sistem(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->get('/super-admin/konfigurasi/konfigurasi-sistem');

        $response->assertOk();
        $response->assertViewIs('super_admin.konfigurasiSistem');
    }

    public function test_index_mengembalikan_data_kategori_dan_node(): void
    {
        $user = $this->createUser('super_admin');
        $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->get('/super-admin/konfigurasi/konfigurasi-sistem');

        $response->assertOk();
        $response->assertViewHas('kategorisData');
        $response->assertViewHas('bidangsData');
        $response->assertViewHas('articlesData');
    }

    // ─── Store Kategori ───────────────────────────────────────────

    public function test_super_admin_dapat_menambah_kategori_sistem(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/kategori', [
                'nama_kategori' => 'Masalah Jaringan',
                'deskripsi'     => 'Kategori untuk masalah jaringan komputer',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['id', 'nama_kategori', 'nodes']);

        $this->assertDatabaseHas('kategori_sistem', ['nama_kategori' => 'Masalah Jaringan']);
    }

    public function test_storeKategori_validasi_gagal_jika_nama_kosong(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/kategori', [
                'nama_kategori' => '',
            ]);

        $response->assertUnprocessable();
    }

    // ─── Update Kategori ──────────────────────────────────────────

    public function test_super_admin_dapat_memperbarui_kategori_sistem(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem(['nama_kategori' => 'Nama Lama']);

        $response = $this->actingAs($user)
            ->putJson("/super-admin/konfigurasi/kategori/{$kategori->id}", [
                'nama_kategori' => 'Nama Diperbarui',
            ]);

        $response->assertOk();
        $response->assertJsonPath('nama_kategori', 'Nama Diperbarui');

        $this->assertEquals('Nama Diperbarui', $kategori->fresh()->nama_kategori);
    }

    public function test_updateKategori_mengembalikan_404_jika_kategori_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->putJson('/super-admin/konfigurasi/kategori/' . Str::uuid(), [
                'nama_kategori' => 'Test',
            ]);

        $response->assertNotFound();
    }

    // ─── Destroy Kategori ─────────────────────────────────────────

    public function test_super_admin_dapat_menghapus_kategori_sistem(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->deleteJson("/super-admin/konfigurasi/kategori/{$kategori->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $this->assertDatabaseMissing('kategori_sistem', ['id' => $kategori->id]);
    }

    public function test_destroyKategori_juga_menghapus_node_terkait(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $node = NodeDiagnosis::create([
            'id'              => (string) Str::uuid(),
            'kategori_id'     => $kategori->id,
            'tipe_node'       => 'pertanyaan',
            'teks_pertanyaan' => 'Test pertanyaan',
        ]);

        $this->actingAs($user)
            ->deleteJson("/super-admin/konfigurasi/kategori/{$kategori->id}");

        $this->assertDatabaseMissing('node_diagnosis', ['id' => $node->id]);
    }

    // ─── Store Node ───────────────────────────────────────────────

    public function test_super_admin_dapat_menambah_node_pertanyaan(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/node', [
                'kategori_id'     => $kategori->id,
                'tipe_node'       => 'pertanyaan',
                'teks_pertanyaan' => 'Apakah komputer dapat menyala?',
            ]);

        $response->assertOk();
        $response->assertJsonStructure(['node', 'new_nodes', 'deleted_node_ids']);

        $this->assertDatabaseHas('node_diagnosis', [
            'kategori_id' => $kategori->id,
            'tipe_node'   => 'pertanyaan',
        ]);
    }

    public function test_super_admin_dapat_menambah_node_solusi(): void
    {
        $user     = $this->createUser('super_admin');
        $bidang   = $this->createBidang();
        $kategori = $this->createKategoriSistem();
        $kb       = $this->createKnowledgeBase();

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/node', [
                'kategori_id'  => $kategori->id,
                'tipe_node'    => 'solusi',
                'judul_solusi' => 'Restart komputer',
                'kb_id'        => $kb->id,
                'bidang_id'    => $bidang->id,
            ]);

        $response->assertOk();
        $this->assertDatabaseHas('node_diagnosis', [
            'tipe_node' => 'solusi',
            'kb_id'     => $kb->id,
        ]);
    }

    public function test_storeNode_validasi_gagal_jika_kategori_tidak_valid(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/node', [
                'kategori_id' => Str::uuid(),
                'tipe_node'   => 'pertanyaan',
            ]);

        $response->assertUnprocessable();
    }

    public function test_storeNode_membuat_child_nodes_untuk_routing_pertanyaan(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $response = $this->actingAs($user)
            ->postJson('/super-admin/konfigurasi/node', [
                'kategori_id'        => $kategori->id,
                'tipe_node'          => 'pertanyaan',
                'teks_pertanyaan'    => 'Apakah ada error?',
                'routing_ya_type'    => 'pertanyaan',
                'routing_tidak_type' => 'solusi',
            ]);

        $response->assertOk();

        $data = $response->json();
        $this->assertNotEmpty($data['new_nodes']);
    }

    // ─── Update Node ──────────────────────────────────────────────

    public function test_super_admin_dapat_memperbarui_node(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $node = NodeDiagnosis::create([
            'id'              => (string) Str::uuid(),
            'kategori_id'     => $kategori->id,
            'tipe_node'       => 'pertanyaan',
            'teks_pertanyaan' => 'Pertanyaan Lama',
        ]);

        $response = $this->actingAs($user)
            ->putJson("/super-admin/konfigurasi/node/{$node->id}", [
                'tipe_node'       => 'pertanyaan',
                'teks_pertanyaan' => 'Pertanyaan Diperbarui',
            ]);

        $response->assertOk();
        $this->assertEquals('Pertanyaan Diperbarui', $node->fresh()->teks_pertanyaan);
    }

    public function test_updateNode_mengembalikan_404_jika_node_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->putJson('/super-admin/konfigurasi/node/' . Str::uuid(), [
                'tipe_node' => 'pertanyaan',
            ]);

        $response->assertNotFound();
    }

    // ─── Destroy Node ─────────────────────────────────────────────

    public function test_super_admin_dapat_menghapus_node(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $node = NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategori->id,
            'tipe_node'   => 'pertanyaan',
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/super-admin/konfigurasi/node/{$node->id}");

        $response->assertOk();
        $response->assertJson(['success' => true]);
        $response->assertJsonStructure(['success', 'deleted_node_ids']);

        $this->assertDatabaseMissing('node_diagnosis', ['id' => $node->id]);
    }

    public function test_destroyNode_juga_menghapus_child_nodes_secara_rekursif(): void
    {
        $user     = $this->createUser('super_admin');
        $kategori = $this->createKategoriSistem();

        $childNode = NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategori->id,
            'tipe_node'   => 'solusi',
        ]);

        $parentNode = NodeDiagnosis::create([
            'id'          => (string) Str::uuid(),
            'kategori_id' => $kategori->id,
            'tipe_node'   => 'pertanyaan',
            'id_next_ya'  => $childNode->id,
        ]);

        $response = $this->actingAs($user)
            ->deleteJson("/super-admin/konfigurasi/node/{$parentNode->id}");

        $response->assertOk();

        $this->assertDatabaseMissing('node_diagnosis', ['id' => $parentNode->id]);
        $this->assertDatabaseMissing('node_diagnosis', ['id' => $childNode->id]);
    }

    public function test_destroyNode_mengembalikan_404_jika_node_tidak_ada(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)
            ->deleteJson('/super-admin/konfigurasi/node/' . Str::uuid());

        $response->assertNotFound();
    }
}
