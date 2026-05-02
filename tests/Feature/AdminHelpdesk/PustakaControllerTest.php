<?php

namespace Tests\Feature\AdminHelpdesk;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class PustakaControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_pustaka_admin_helpdesk(): void
    {
        $this->get('/admin-helpdesk/pustaka')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_pustaka_admin_helpdesk(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/admin-helpdesk/pustaka')->assertForbidden();
    }

    // ─── Index ────────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_pustaka(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/pustaka');

        $response->assertOk();
    }

    public function test_pustaka_menampilkan_artikel_sesuai_bidang_admin(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)->get('/admin-helpdesk/pustaka');

        $response->assertOk();
    }

    // ─── Show ─────────────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_melihat_detail_artikel_pustaka(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);
        $artikel = $this->createKnowledgeBase([
            'bidang_id'         => $bidang->id,
            'visibilitas_akses' => 'internal',
        ]);

        $response = $this->actingAs($user)->get("/admin-helpdesk/pustaka/{$artikel->id}");

        $response->assertOk();
    }

    public function test_show_mengembalikan_404_jika_artikel_tidak_ditemukan(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/pustaka/' . Str::uuid());

        $response->assertNotFound();
    }
}
