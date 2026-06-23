<?php
namespace Tests\Unit\AdminHelpdesk;
use App\Http\Controllers\AdminHelpdesk\ChatController;
use Illuminate\Http\Request;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    public function testShowTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/tiket/FAKE/chat')->status(),[302,403]), 'show() — role non-admin ditolak akses chat');
    }
    public function testShowChatAktif(): void
    {
        $c = new FakeAdminChatController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockTiket=FakeACTiket::make(['panduan_remote']);
        $c->mockRoom=(object)['id'=>'ROOM-001','nama_roomchat'=>'admin'];
        $c->mockMessages=collect([['id'=>'M1','konten'=>'Halo']]);
        $r=$c->show('TKT-001');
        $this->assertSame('admin_helpdesk.chat',$r['view']);
        $this->assertTrue($r['data']['chatIsActive']);
        $this->assertTrue($c->roomUserCreated, 'show() — halaman chat aktif ditampilkan dengan benar');
    }
    public function testSendTolakNonaktif(): void
    {
        $c = new FakeAdminChatController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockTiket=FakeACTiket::make(['selesai']);
        $r=$c->send(Request::create('/c','POST',['konten'=>'Hi']),'TKT-001');
        $this->assertSame(403,$r['status'], 'send() — pesan ditolak saat chat tidak aktif');
    }
    public function testSendPesanText(): void
    {
        $c = new FakeAdminChatController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockTiket=FakeACTiket::make(['panduan_remote']);
        $c->mockRoom=(object)['id'=>'ROOM-001'];
        $c->mockSenderName='Admin';
        $r=$c->send(Request::create('/c','POST',['konten'=>'Bantuan']),'TKT-001');
        $this->assertSame(200,$r['status']);
        $this->assertSame('Bantuan',$c->createdMsg['konten']);
        $this->assertTrue($c->broadcasted, 'send() — pesan text berhasil dikirim dan di-broadcast');
    }
    public function testSendValidasiKonten(): void
    {
        $c = new FakeAdminChatController();
        $c->mockAdmin=(object)['id'=>'AH-001','bidang_id'=>'BDG-001'];
        $c->mockTiket=FakeACTiket::make(['panduan_remote']);
        $c->mockRoom=(object)['id'=>'ROOM-001'];
        try{$c->send(Request::create('/c','POST',[]),'TKT-001');$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('konten',$e->errors(), 'send() — validasi konten wajib diisi');}
    }
}
class FakeAdminChatController extends ChatController{
    public $mockAdmin,$mockTiket,$mockRoom,$mockMessages;
    public ?string $mockSenderName='Admin';
    public bool $roomUserCreated=false,$roomRead=false,$broadcasted=false;
    public ?array $createdMsg=null;
    protected function authenticatedUserId():?string{return 'U1';}
    protected function findAdminProfile(){return $this->mockAdmin;}
    protected function findTiketForAdmin(string $t,?string $a){return $this->mockTiket;}
    protected function firstOrCreateRoom(string $t,?string $b){return $this->mockRoom;}
    protected function findExistingRoom(string $t){return $this->mockRoom;}
    protected function ensureRoomUser(string $r,string $u,string $role,?string $b):void{$this->roomUserCreated=true;}
    protected function markRoomAsRead(string $r,string $u):void{$this->roomRead=true;}
    protected function loadRoomMessages(string $r){return $this->mockMessages??collect();}
    protected function createMessage(array $a){$this->createdMsg=$a;return(object)array_merge($a,['created_at'=>'2026-05-17 20:00:00']);}
    protected function senderName():string{return $this->mockSenderName;}
    protected function broadcastMessage($m,string $s):void{$this->broadcasted=true;}
    protected function renderView(string $v,array $d){return['view'=>$v,'data'=>$d];}
    protected function jsonResponse(array $d,int $s=200){return['data'=>$d,'status'=>$s];}
}
class FakeACTiket{
    public string $id='TKT-001',$bidang_id='BDG-001';
    public $opd,$statusTiket;
    public static function make(array $st):self{
        $t=new self();$t->opd=(object)['user_id'=>'U-OPD'];
        $t->statusTiket=collect($st)->map(fn($s,$i)=>(object)['status_tiket'=>$s,'created_at'=>now()->addSeconds($i)]);
        return $t;
    }
}
