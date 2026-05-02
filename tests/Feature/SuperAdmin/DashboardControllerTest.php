<?php

namespace Tests\Feature\SuperAdmin;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_dashboard_super_admin(): void
    {
        $response = $this->get('/super-admin/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_dashboard_super_admin(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/super-admin/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_dashboard_super_admin(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/super-admin/dashboard');

        $response->assertForbidden();
    }

    public function test_pimpinan_tidak_dapat_mengakses_dashboard_super_admin(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/super-admin/dashboard');

        $response->assertForbidden();
    }

    // ─── Happy Path ───────────────────────────────────────────────

    public function test_super_admin_dapat_mengakses_dashboard(): void
    {
        $user = $this->createUser('super_admin');

        $response = $this->actingAs($user)->get('/super-admin/dashboard');

        $response->assertOk();
        $response->assertViewIs('super_admin.dashboard');
    }

    public function test_dashboard_super_admin_menampilkan_statistik_global(): void
    {
        $user = $this->createUser('super_admin');

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)->get('/super-admin/dashboard');

        $response->assertOk();
    }
}
