<?php
namespace Tests\Unit\TimTeknis;
use App\Http\Controllers\TimTeknis\PustakaController;
use Illuminate\Http\Request;
use Tests\TestCase;

class PustakaControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/tim-teknis/pustaka')->status(),[302,403]), 'index() — role non-tim_teknis ditolak');
    }
    public function testIndexDaftarSop(): void
    {
        $c = new FakeTTPustakaController();
        $c->mockTeknis=(object)['id'=>'TT-001','bidang_id'=>'BDG-001'];
        $c->mockArticles=collect([(object)['judul'=>'SOP Jaringan']]);
        $c->mockKategoris=collect([(object)['nama_kategori'=>'Jaringan']]);
        $r=$c->index(Request::create('/p'));
        $this->assertSame('tim_teknis.pustaka',$r['view']);
        $this->assertCount(1,$r['data']['articles'], 'index() — daftar SOP ditampilkan');
    }
    public function testIndexFilterPencarian(): void
    {
        $c = new FakeTTPustakaController();
        $c->mockTeknis=(object)['id'=>'TT-001'];
        $r=$c->index(Request::create('/p','GET',['search'=>'jaringan']));
        $this->assertSame('jaringan',$r['data']['search']);
        $this->assertSame('jaringan',$c->queriedSearch, 'index() — filter pencarian berfungsi');
    }
    public function testShowDetailSop(): void
    {
        $art=(object)['id'=>'SOP-001','judul'=>'SOP Test'];
        $c = new FakeTTPustakaController();
        $c->mockTeknis=(object)['id'=>'TT-001']; $c->mockArticle=$art;
        $r=$c->show('SOP-001');
        $this->assertSame('tim_teknis.pustaka-show',$r['view']);
        $this->assertSame($art,$r['data']['article'], 'show() — detail SOP ditampilkan');
    }
}
class FakeTTPustakaController extends PustakaController{
    public $mockTeknis,$mockArticles,$mockKategoris,$mockArticle;
    public ?string $queriedSearch=null,$requestedId=null;
    protected function findTeknisProfile(){return $this->mockTeknis;}
    protected function queryArticles(string $s,string $f){$this->queriedSearch=$s?:null;return $this->mockArticles??collect();}
    protected function allKategoris(){return $this->mockKategoris??collect();}
    protected function findArticleOrFail(string $id){$this->requestedId=$id;return $this->mockArticle;}
    protected function renderView(string $v,array $d){return['view'=>$v,'data'=>$d];}
}
