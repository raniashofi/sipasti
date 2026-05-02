<?php

namespace Tests\Feature\AdminHelpdesk;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_dashboard_admin_helpdesk(): void
    {
        $response = $this->get('/admin-helpdesk/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_dashboard_admin_helpdesk(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/admin-helpdesk/dashboard');

        $response->assertForbidden();
    }

    public function test_tim_teknis_tidak_dapat_mengakses_dashboard_admin_helpdesk(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/dashboard');

        $response->assertForbidden();
    }

    // ─── Happy Path ───────────────────────────────────────────────

    public function test_admin_helpdesk_dapat_mengakses_dashboard(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/admin-helpdesk/dashboard');

        $response->assertOk();
        $response->assertViewIs('admin_helpdesk.dashboard');
    }

    public function test_dashboard_admin_helpdesk_menampilkan_statistik(): void
    {
        $bidang   = $this->createBidang();
        $user     = $this->createUser('admin_helpdesk');
        $adminAh  = $this->createAdminHelpdesk($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd, ['admin_id' => $adminAh->id]);
        $this->createStatusTiket($tiket, 'panduan_remote');

        $response = $this->actingAs($user)->get('/admin-helpdesk/dashboard');

        $response->assertOk();
    }
}
