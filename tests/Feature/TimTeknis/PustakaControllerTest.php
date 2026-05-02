<?php

namespace Tests\Feature\TimTeknis;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class PustakaControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_pustaka_tim_teknis(): void
    {
        $this->get('/tim-teknis/pustaka')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_pustaka_tim_teknis(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/tim-teknis/pustaka')->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_tim_teknis_dapat_mengakses_halaman_pustaka(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/pustaka');

        $response->assertOk();
    }

    public function test_pustaka_menampilkan_artikel_internal_sesuai_bidang(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)->get('/tim-teknis/pustaka');

        $response->assertOk();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_tim_teknis_dapat_melihat_detail_artikel_pustaka(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);
        $artikel = $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)->get("/tim-teknis/pustaka/{$artikel->id}");

        $response->assertOk();
    }

    public function test_show_mengembalikan_404_jika_artikel_tidak_ditemukan(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/pustaka/' . Str::uuid());

        $response->assertNotFound();
    }
}
