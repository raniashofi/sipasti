<?php
namespace Tests\Unit\TimTeknis;
use App\Http\Controllers\TimTeknis\DashboardController;
use Tests\TestCase;

class DashboardControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/tim-teknis/dashboard')->status(),[302,403]), 'index() — role non-tim_teknis ditolak');
    }
    public function testIndexAbortTanpaProfil(): void
    {
        $c = new FakeTTDashboardController(); $c->mockTeknis=null;
        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);
        $c->index();
    }
    public function testIndexStatistik(): void
    {
        $c = new FakeTTDashboardController();
        $c->mockTeknis=(object)['id'=>'TT-001','bidang'=>(object)['batas_hari_pengerjaan'=>3]];
        $c->mockStats=['aktif'=>2,'total_selesai'=>5,'tepat_waktu'=>4,'telat'=>1];
        $c->mockActiveTickets=collect(['TKT-001','TKT-002']);
        $c->mockDistribution=collect([(object)['bulan'=>5,'jumlah'=>3]]);
        $c->mockLastLogin='17 Mei 2026, 20:00';
        $r=$c->index();
        $this->assertSame('tim_teknis.dashboard',$r['view']);
        $this->assertSame(2,$r['data']['stats']['aktif']);
        $this->assertCount(2,$r['data']['tiketAktif'], 'index() — statistik dan tiket aktif ditampilkan');
    }
    public function testIndexRedirectException(): void
    {
        $c = new FakeTTDashboardController();
        $c->mockTeknis=(object)['id'=>'TT-001']; $c->throwOnStats=true;
        $r=$c->index();
        $this->assertInstanceOf(\Illuminate\Http\RedirectResponse::class,$r);
        $this->assertNotNull($c->loggedError, 'index() — redirect saat exception terjadi');
    }
}
class FakeTTDashboardController extends DashboardController{
    public $mockTeknis=null;public ?string $mockLastLogin=null;
    public array $mockStats=[];public $mockActiveTickets,$mockDistribution;
    public bool $throwOnStats=false;public ?string $loggedError=null;
    protected function findTeknisProfile(){return $this->mockTeknis;}
    protected function lastLoginFormatted(){return $this->mockLastLogin;}
    protected function buildStats($t):array{if($this->throwOnStats)throw new \RuntimeException('Err');return $this->mockStats;}
    protected function activeTickets(string $id){return $this->mockActiveTickets??collect();}
    protected function completedDistribution(string $id){return $this->mockDistribution??collect();}
    protected function logError(string $m):void{$this->loggedError=$m;}
    protected function renderView(string $v,array $d){return['view'=>$v,'data'=>$d];}
}
