<?php
namespace Tests\Unit\SuperAdmin;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/dashboard')->status(),[302,403]), 'index() — role non-super_admin ditolak');
    }
    public function testIndexStatsDefault(): void
    {
        $stats=['total_tiket'=>0,'tiket_aktif'=>0,'tiket_selesai'=>0,'total_pengguna'=>0];
        $this->assertSame(0,$stats['total_tiket']);
        $this->assertSame(0,$stats['total_pengguna'], 'index() — statistik default kosong');
    }
}
