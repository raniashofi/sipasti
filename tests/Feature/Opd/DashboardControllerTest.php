<?php

namespace Tests\Feature\Opd;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_dashboard_opd(): void
    {
        $response = $this->get('/opd/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_dashboard_opd(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertForbidden();
    }

    public function test_tim_teknis_tidak_dapat_mengakses_dashboard_opd(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertForbidden();
    }

    public function test_super_admin_tidak_dapat_mengakses_dashboard_opd(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertForbidden();
    }

    // ─── Happy Path ───────────────────────────────────────────────

    public function test_opd_dapat_mengakses_dashboard(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertOk();
        $response->assertViewIs('opd.dashboard');
    }

    public function test_dashboard_menampilkan_statistik_tiket(): void
    {
        $user = $this->createUser('opd');
        $opd  = $this->createOpd($user);

        $tiket1 = $this->createTiket($opd);
        $this->createStatusTiket($tiket1, 'panduan_remote');

        $tiket2 = $this->createTiket($opd);
        $this->createStatusTiket($tiket2, 'selesai');

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertOk();
        $response->assertViewHas('tiketAktif');
        $response->assertViewHas('tiketSelesai');
        $response->assertViewHas('tiketTotal');
    }

    public function test_dashboard_opd_tanpa_profil_tetap_dapat_diakses(): void
    {
        $user = $this->createUser('opd');

        $response = $this->actingAs($user)->get('/opd/dashboard');

        $response->assertOk();
    }
}
