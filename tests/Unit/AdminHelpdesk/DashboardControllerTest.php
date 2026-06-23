<?php
namespace Tests\Unit\AdminHelpdesk;
use App\Http\Controllers\AdminHelpdesk\DashboardController;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $r = $this->get('/admin-helpdesk/dashboard');
        $this->assertTrue(in_array($r->status(),[302,403]), 'index() — role non-admin ditolak akses dashboard');
    }

    public function testIndexTanpaAdmin(): void
    {
        $c = new FakeAdminDashboardController();
        $c->mockAdminProfile = null;
        $result = $c->index();
        $this->assertSame('admin_helpdesk.dashboard', $result['view']);
        $this->assertNull($result['data']['adminProfile']);
        $this->assertSame(0, $result['data']['stats']['menunggu_verif']);
        $this->assertSame(0, $result['data']['stats']['panduan_remote']);
        $this->assertSame(0, $result['data']['stats']['eskalasi']);
        $this->assertSame(0, $result['data']['stats']['selesai'], 'index() — dashboard kosong jika admin profile null');
    }

    public function testIndexStatistikLengkap(): void
    {
        $c = new FakeAdminDashboardController();
        $c->mockAdminProfile = (object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockMenungguVerif=5; $c->mockPanduanRemote=3; $c->mockDistribusi=4;
        $c->mockSelesai=10; $c->mockRusakBerat=2; $c->mockSelesaiDitutup=8;
        $c->mockPublishedSop=12; $c->mockRecentActivity=collect(['act1','act2']);
        $result = $c->index();
        $this->assertSame('admin_helpdesk.dashboard', $result['view']);
        $this->assertSame(5, $result['data']['stats']['menunggu_verif']);
        $this->assertSame(3, $result['data']['stats']['panduan_remote']);
        $this->assertSame(4, $result['data']['stats']['eskalasi']);
        $this->assertSame(12, $result['data']['stats']['total_kb']);
        $this->assertSame(['act1','act2'], $result['data']['recentActivity']->all(), 'index() — semua statistik ditampilkan dengan benar');
    }
}

class FakeAdminDashboardController extends DashboardController
{
    public $mockAdminProfile=null;
    public int $mockMenungguVerif=0,$mockPanduanRemote=0,$mockDistribusi=0;
    public int $mockSelesai=0,$mockRusakBerat=0,$mockSelesaiDitutup=0,$mockPublishedSop=0;
    public $mockRecentActivity;
    protected function findAdminProfile(){return $this->mockAdminProfile;}
    protected function countTiketByAdmin(?string $a,callable $s):int{
        static $i=0;$v=[$this->mockPanduanRemote,$this->mockSelesai,$this->mockRusakBerat,$this->mockSelesaiDitutup];return $v[$i++%4]??0;
    }
    protected function countPublishedSop():int{return $this->mockPublishedSop;}
    protected function countDistribusi(?string $a):int{return $this->mockDistribusi;}
    protected function recentAdminActivity(){return $this->mockRecentActivity??collect();}
    protected function menungguVerifikasiIds($a,callable $f){return collect(array_fill(0,$this->mockMenungguVerif,'M'));}
    protected function renderView(string $v,array $d){return['view'=>$v,'data'=>$d];}
}
