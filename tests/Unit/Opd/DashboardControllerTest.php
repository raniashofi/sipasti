<?php

namespace Tests\Unit\Opd;

use App\Http\Controllers\Opd\DashboardController;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{

    public function testIndexTolakNonOpd()
    {
        // Membuat objek user di RAM tanpa menyimpannya ke database
        $userMock = new \App\Models\User(['id' => 999, 'role' => 'admin_helpdesk']);
        
        // Memasangkan user tersebut ke session aplikasi (standard Laravel mocking)
        $this->actingAs($userMock);

        $response = $this->get('/opd/dashboard');
        
        // Jika route diarahkan ke login atau dilarang
        if ($response->status() === 302 || $response->status() === 403) {
            $this->assertTrue(in_array($response->status(), [302, 403]));
        } else {
            $response->assertForbidden();
        }
    }
    public function testIndexTanpaOpd(): void
    {
        $controller = new FakeDashboardController();
        $controller->mockUser = (object) ['id' => 'USR-001', 'opd' => null];

        $result = $controller->index();

        $this->assertSame('opd.dashboard', $result['view']);
        $this->assertNull($result['data']['opd']);
        $this->assertSame(['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0], $result['data']['stats']);
        $this->assertSame(0, $result['data']['tiketTotal']);
        $this->assertTrue($result['data']['tiketTerbaru']->isEmpty());
    }

    public function testIndexStatistik(): void
    {
        $opd = (object) ['id' => 'OPD-001', 'nama_opd' => 'Dinas Kominfo'];
        $controller = new FakeDashboardController();
        $controller->mockUser = (object) ['id' => 'USR-001', 'opd' => $opd];
        $controller->mockStats = ['total' => 8, 'aktif' => 3, 'revisi' => 1, 'selesai' => 4];
        $controller->mockLatestTickets = collect(['TKT-001', 'TKT-002']);
        $controller->mockLastLogin = '17 Mei 2026, 20:00';

        $result = $controller->index();

        $this->assertSame('opd.dashboard', $result['view']);
        $this->assertSame($opd, $result['data']['opd']);
        $this->assertSame($controller->mockStats, $result['data']['stats']);
        $this->assertSame(3, $result['data']['tiketAktif']);
        $this->assertSame(4, $result['data']['tiketSelesai']);
        $this->assertSame(8, $result['data']['tiketTotal']);
        $this->assertSame(['TKT-001', 'TKT-002'], $result['data']['tiketTerbaru']->all());
        $this->assertSame('17 Mei 2026, 20:00', $result['data']['lastLogin']);
        $this->assertSame('USR-001', $controller->requestedLastLoginUserId);
        $this->assertSame('OPD-001', $controller->requestedStatsOpdId);
        $this->assertSame('OPD-001', $controller->requestedLatestTicketsOpdId);
    }

    public function testIndexException(): void
    {
        $controller = new FakeDashboardController();
        $controller->mockUser = (object) ['id' => 'USR-001', 'opd' => (object) ['id' => 'OPD-001']];
        $controller->throwOnStats = true;

        $result = $controller->index();

        $this->assertSame(['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0], $result['data']['stats']);
        $this->assertSame(0, $result['data']['tiketAktif']);
        $this->assertTrue($result['data']['tiketTerbaru']->isEmpty());
        $this->assertStringContainsString('Opd Dashboard Error:', $controller->loggedError);
    }
}

class FakeDashboardController extends DashboardController
{
    public $mockUser;
    public array $mockStats = ['total' => 0, 'aktif' => 0, 'revisi' => 0, 'selesai' => 0];
    public $mockLatestTickets;
    public ?string $mockLastLogin = null;
    public ?string $requestedLastLoginUserId = null;
    public ?string $requestedStatsOpdId = null;
    public ?string $requestedLatestTicketsOpdId = null;
    public bool $throwOnStats = false;
    public ?string $loggedError = null;

    protected function authenticatedUser()
    {
        return $this->mockUser;
    }

    protected function lastLoginFormatted(string $userId)
    {
        $this->requestedLastLoginUserId = $userId;
        return $this->mockLastLogin;
    }

    protected function ticketStats(string $opdId): array
    {
        $this->requestedStatsOpdId = $opdId;

        if ($this->throwOnStats) {
            throw new \RuntimeException('Query gagal');
        }

        return $this->mockStats;
    }

    protected function latestTickets(string $opdId)
    {
        $this->requestedLatestTicketsOpdId = $opdId;
        return $this->mockLatestTickets ?? collect();
    }

    protected function logError(string $message): void
    {
        $this->loggedError = $message;
    }

    protected function renderView(string $view, array $data)
    {
        return ['view' => $view, 'data' => $data];
    }
}
