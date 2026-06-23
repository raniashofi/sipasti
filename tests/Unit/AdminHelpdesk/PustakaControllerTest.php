<?php
namespace Tests\Unit\AdminHelpdesk;
use App\Http\Controllers\AdminHelpdesk\PustakaController;
use Illuminate\Http\Request;
use Tests\TestCase;

class PustakaControllerTest extends TestCase
{
    public function testIndexTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/pustaka')->status(),[302,403]), 'index() — role non-admin ditolak akses pustaka');
    }
    public function testIndexDaftarSop(): void
    {
        $c = new FakeAdminPustakaController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockArticles=collect([(object)['judul'=>'SOP Jaringan'],(object)['judul'=>'SOP Printer']]);
        $c->mockKategoris=collect([(object)['nama_kategori'=>'Jaringan']]);
        $r=$c->index(Request::create('/pustaka'));
        $this->assertSame('admin_helpdesk.pustaka.index',$r['view']);
        $this->assertCount(2,$r['data']['articles'], 'index() — daftar SOP ditampilkan');
    }
    public function testIndexFilterPencarian(): void
    {
        $c = new FakeAdminPustakaController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockArticles=collect([(object)['judul'=>'SOP Jaringan']]);
        $r=$c->index(Request::create('/p','GET',['search'=>'jaringan']));
        $this->assertSame('jaringan',$r['data']['search']);
        $this->assertSame('jaringan',$c->queriedSearch, 'index() — filter pencarian berfungsi');
    }
    public function testShowDetailSop(): void
    {
        $art=(object)['id'=>'SOP-001','judul'=>'SOP Jaringan'];
        $c = new FakeAdminPustakaController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockArticle=$art;
        $r=$c->show('SOP-001');
        $this->assertSame('admin_helpdesk.pustaka.show',$r['view']);
        $this->assertSame($art,$r['data']['article'], 'show() — detail SOP ditampilkan');
    }
}
class FakeAdminPustakaController extends PustakaController{
    public $mockAdmin,$mockArticles,$mockKategoris,$mockArticle;
    public ?string $queriedBidangId=null,$queriedSearch=null,$requestedArticleId=null,$requestedBidangId=null;
    protected function findAdminProfile(){return $this->mockAdmin;}
    protected function queryArticles(?string $b,string $s,string $f){$this->queriedBidangId=$b;$this->queriedSearch=$s?:null;return $this->mockArticles??collect();}
    protected function allKategoris(){return $this->mockKategoris??collect();}
    protected function findArticleForAdmin(string $id,?string $b){$this->requestedArticleId=$id;$this->requestedBidangId=$b;return $this->mockArticle;}
    protected function renderView(string $v,array $d){return['view'=>$v,'data'=>$d];}
}
