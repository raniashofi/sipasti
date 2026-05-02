<?php

namespace Tests\Feature\Pimpinan;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestModels;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    use RefreshDatabase, CreatesTestModels;

    // ─── Auth & Role Guard ────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_dashboard_pimpinan(): void
    {
        $this->get('/pimpinan/dashboard')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_dashboard_pimpinan(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/pimpinan/dashboard')->assertForbidden();
    }

    public function test_admin_helpdesk_tidak_dapat_mengakses_dashboard_pimpinan(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($user, $bidang);

        $this->actingAs($user)->get('/pimpinan/dashboard')->assertForbidden();
    }

    public function test_tim_teknis_tidak_dapat_mengakses_dashboard_pimpinan(): void
    {
        $bidang = $this->createBidang();
        $user   = $this->createUser('tim_teknis');
        $this->createTimTeknis($user, $bidang);

        $this->actingAs($user)->get('/pimpinan/dashboard')->assertForbidden();
    }

    public function test_super_admin_tidak_dapat_mengakses_dashboard_pimpinan(): void
    {
        $user = $this->createUser('super_admin');

        $this->actingAs($user)->get('/pimpinan/dashboard')->assertForbidden();
    }

    // ─── Index (Happy Path) ───────────────────────────────────────

    public function test_pimpinan_dapat_mengakses_dashboard(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewIs('pimpinan.dashboard');
    }

    public function test_dashboard_memuat_semua_variabel_yang_dibutuhkan_view(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalTiket');
        $response->assertViewHas('tiketAktif');
        $response->assertViewHas('tiketSelesai');
        $response->assertViewHas('avgKepuasan');
        $response->assertViewHas('totalOpd');
        $response->assertViewHas('totalKb');
        $response->assertViewHas('tiketPerStatus');
        $response->assertViewHas('trendMonths');
        $response->assertViewHas('tiketPerBidang');
        $response->assertViewHas('performanceAdmin');
        $response->assertViewHas('workloadTeknis');
        $response->assertViewHas('auditLog');
        $response->assertViewHas('kpiData');
        $response->assertViewHas('dateFrom');
        $response->assertViewHas('dateTo');
        $response->assertViewHas('period');
    }

    // ─── Filter Periode ───────────────────────────────────────────

    public function test_dashboard_menggunakan_periode_monthly_secara_default(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('period', 'monthly');
    }

    public function test_dashboard_dapat_difilter_dengan_periode_daily(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard?period=daily');

        $response->assertOk();
        $response->assertViewHas('period', 'daily');
    }

    public function test_dashboard_dapat_difilter_dengan_periode_weekly(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard?period=weekly');

        $response->assertOk();
        $response->assertViewHas('period', 'weekly');
    }

    public function test_dashboard_dapat_difilter_dengan_periode_yearly(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard?period=yearly');

        $response->assertOk();
        $response->assertViewHas('period', 'yearly');
    }

    public function test_dashboard_dapat_difilter_dengan_periode_custom(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard?period=custom&date_from=2026-01-01&date_to=2026-01-31');

        $response->assertOk();
        $response->assertViewHas('period', 'custom');
        $response->assertViewHas('dateFrom', '2026-01-01');
        $response->assertViewHas('dateTo', '2026-01-31');
    }

    // ─── Dashboard with Data ──────────────────────────────────────

    public function test_dashboard_menampilkan_data_tiket_dalam_periode(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket   = $this->createTiket($opd);
        $this->createStatusTiket($tiket, 'verifikasi_admin');

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('totalTiket');
    }

    public function test_dashboard_menghitung_rata_rata_kepuasan(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser);
        $tiket1  = $this->createTiket($opd, ['penilaian' => 4]);
        $tiket2  = $this->createTiket($opd, ['penilaian' => 5]);
        $this->createStatusTiket($tiket1, 'tiket_ditutup');
        $this->createStatusTiket($tiket2, 'tiket_ditutup');

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('avgKepuasan');
    }

    public function test_dashboard_menampilkan_data_performa_admin_helpdesk(): void
    {
        $user   = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $bidang   = $this->createBidang();
        $ahUser   = $this->createUser('admin_helpdesk');
        $this->createAdminHelpdesk($ahUser, $bidang);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('performanceAdmin');
    }

    public function test_dashboard_menampilkan_beban_kerja_tim_teknis(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $bidang  = $this->createBidang();
        $ttUser  = $this->createUser('tim_teknis');
        $this->createTimTeknis($ttUser, $bidang);

        $response = $this->actingAs($user)->get('/pimpinan/dashboard');

        $response->assertOk();
        $response->assertViewHas('workloadTeknis');
    }

    // ─── Export CSV ───────────────────────────────────────────────

    public function test_tamu_tidak_dapat_mengakses_export_csv(): void
    {
        $this->get('/pimpinan/export/csv')->assertRedirect('/login');
    }

    public function test_opd_tidak_dapat_mengakses_export_csv(): void
    {
        $user = $this->createUser('opd');
        $this->createOpd($user);

        $this->actingAs($user)->get('/pimpinan/export/csv')->assertForbidden();
    }

    public function test_pimpinan_dapat_mengunduh_laporan_csv(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)->get('/pimpinan/export/csv');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('attachment', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('.csv', $response->headers->get('Content-Disposition'));
    }

    public function test_export_csv_dengan_filter_tanggal(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $response = $this->actingAs($user)
            ->get('/pimpinan/export/csv?date_from=2026-01-01&date_to=2026-01-31');

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('Content-Type'));
        $this->assertStringContainsString('2026-01-01', $response->headers->get('Content-Disposition'));
        $this->assertStringContainsString('2026-01-31', $response->headers->get('Content-Disposition'));
    }

    public function test_export_csv_berisi_data_tiket(): void
    {
        $user = $this->createUser('pimpinan');
        $this->createPimpinan($user);

        $opdUser = $this->createUser('opd');
        $opd     = $this->createOpd($opdUser, ['nama_opd' => 'Dinas Test Ekspor']);
        $tiket   = $this->createTiket($opd, ['subjek_masalah' => 'Masalah Jaringan Ekspor']);
        $this->createStatusTiket($tiket, 'selesai');

        $response = $this->actingAs($user)
            ->get('/pimpinan/export/csv?date_from=' . now()->startOfYear()->toDateString() . '&date_to=' . now()->toDateString());

        $response->assertOk();
        $content = $response->streamedContent();
        $this->assertStringContainsString('ID Tiket', $content);
        $this->assertStringContainsString('OPD', $content);
        $this->assertStringContainsString('Subjek Masalah', $content);
    }
}
