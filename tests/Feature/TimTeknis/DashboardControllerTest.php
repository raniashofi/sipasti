<?php

namespace Tests\Feature\TimTeknis;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_dashboard_tim_teknis(): void
    {
        $response = $this->get('/tim-teknis/dashboard');

        $response->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_dashboard_tim_teknis(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $response = $this->actingAs($user)->get('/tim-teknis/dashboard');

        $response->assertForbidden();
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_dashboard_tim_teknis(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/dashboard');

        $response->assertForbidden();
    }

    // ─── Happy Path ───────────────────────────────────────────────

    public function test_tim_teknis_dapat_mengakses_dashboard(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $response = $this->actingAs($user)->get('/tim-teknis/dashboard');

        $response->assertOk();
        $response->assertViewIs('tim_teknis.dashboard');
    }

    public function test_dashboard_tim_teknis_menampilkan_data_kinerja(): void
    {
        $bidang  = $this->createBidang();
        $user    = $this->createUser('tim_teknis');
        $teknisi = $this->createTimTeknis($user, $bidang);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'perbaikan_teknis');
        $this->createTiketTeknisi($tiket, $teknisi, ['status_tugas' => 'aktif']);

        $response = $this->actingAs($user)->get('/tim-teknis/dashboard');

        $response->assertOk();
    }
}
