<?php

namespace Tests\Unit\Pimpinan;

use App\Http\Controllers\Pimpinan\DashboardController;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testIndexTolakNonPimpinan()
    {
        $this->actingAs(new \App\Models\User(['id' => 999, 'role' => 'opd']));
        $this->assertTrue(in_array($this->get('/pimpinan/dashboard')->status(), [302, 403]), 'index() - role non-pimpinan ditolak');
    }

    public function testIndexFilterDaily(): void
    {
        [$from, $to, $period] = (new TestablePimpinanDashboardController())->callPeriodDateRange(
            Request::create('/pimpinan/dashboard', 'GET', ['period' => 'daily'])
        );

        $this->assertSame('daily', $period);
        $this->assertTrue(Carbon::parse($from)->isSameDay(Carbon::parse($to)), 'index() - filter daily menghasilkan range hari ini');
    }

    public function testIndexFilterWeekly(): void
    {
        [$from, $to, $period] = (new TestablePimpinanDashboardController())->callPeriodDateRange(
            Request::create('/pimpinan/dashboard', 'GET', ['period' => 'weekly'])
        );

        $this->assertSame('weekly', $period);
        $this->assertTrue(Carbon::parse($from)->lt(Carbon::parse($to)), 'index() - filter weekly range seminggu');
    }

    public function testIndexFilterMonthly(): void
    {
        [$from, $to, $period] = (new TestablePimpinanDashboardController())->callPeriodDateRange(
            Request::create('/pimpinan/dashboard')
        );

        $this->assertSame('monthly', $period);
        $this->assertTrue(Carbon::parse($from)->isSameMonth(Carbon::parse($to)), 'index() - filter monthly range sebulan');
    }

    public function testIndexFilterYearly(): void
    {
        [$from, $to, $period] = (new TestablePimpinanDashboardController())->callPeriodDateRange(
            Request::create('/pimpinan/dashboard', 'GET', ['period' => 'yearly'])
        );

        $this->assertSame('yearly', $period);
        $this->assertTrue(Carbon::parse($from)->isSameYear(Carbon::parse($to)), 'index() - filter yearly range setahun');
    }

    public function testIndexFilterCustom(): void
    {
        [$from, $to, $period] = (new TestablePimpinanDashboardController())->callPeriodDateRange(
            Request::create('/pimpinan/dashboard', 'GET', [
                'period' => 'custom',
                'date_from' => '2026-01-01',
                'date_to' => '2026-05-17',
            ])
        );

        $this->assertSame('custom', $period);
        $this->assertSame('2026-01-01', $from);
        $this->assertSame('2026-05-17', $to);
    }

    public function testIndexStatusSelesai(): void
    {
        $statusSelesai = (new TestablePimpinanDashboardController())->callStatusSelesai();

        $this->assertContains('selesai', $statusSelesai);
        $this->assertContains('tiket_ditutup', $statusSelesai, 'index() - tiket_ditutup termasuk selesai');
    }

    public function testIndexKpiData(): void
    {
        $rate = (new TestablePimpinanDashboardController())->callResolutionRate(10, 8);

        $this->assertEquals(80, $rate, 'index() - KPI tingkat resolusi dihitung benar');
        $this->assertEquals(0, (new TestablePimpinanDashboardController())->callResolutionRate(0, 0));
    }

    public function testExportCsvDateRange(): void
    {
        [$from, $to] = (new TestablePimpinanDashboardController())->callExportDateRange(
            Request::create('/pimpinan/dashboard/export', 'GET', [
                'date_from' => '2026-01-01',
                'date_to' => '2026-05-17',
            ])
        );

        $this->assertTrue(Carbon::parse($from)->lt(Carbon::parse($to)), 'exportCsv() - date_from < date_to untuk export');
    }

    public function testExportXlsxFilenameFormat(): void
    {
        $filename = (new TestablePimpinanDashboardController())->callExportFilename('2026-01-01', '2026-05-17');

        $this->assertSame('laporan_kinerja_tiket_2026-01-01_sd_2026-05-17.xlsx', $filename);
    }

    public function testStatusLabelMapping(): void
    {
        $controller = new TestablePimpinanDashboardController();

        $this->assertSame('Verifikasi Admin', $controller->callStatusLabel('verifikasi_admin'), 'statusLabel - mapping label status benar');
        $this->assertSame('Status baru', $controller->callStatusLabel('status baru'));
    }
}

class TestablePimpinanDashboardController extends DashboardController
{
    public function callPeriodDateRange(Request $request): array
    {
        return $this->periodDateRange($request);
    }

    public function callExportDateRange(Request $request): array
    {
        return $this->exportDateRange($request);
    }

    public function callExportFilename(string $dateFrom, string $dateTo): string
    {
        return $this->exportFilename($dateFrom, $dateTo);
    }

    public function callStatusSelesai(): array
    {
        return $this->statusSelesai();
    }

    public function callStatusLabel(string $status): string
    {
        return $this->statusLabel($status);
    }

    public function callResolutionRate(int $totalTiket, int $tiketSelesai): int
    {
        return $this->resolutionRate($totalTiket, $tiketSelesai);
    }
}
