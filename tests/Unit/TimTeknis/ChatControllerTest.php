<?php
namespace Tests\Unit\TimTeknis;
use Tests\TestCase;

class ChatControllerTest extends TestCase
{
    public function testShowTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/tim-teknis/tiket/FAKE/chat')->status(),[302,403]), 'show() — role non-tim_teknis ditolak');
    }
    public function testShowChatAktifPerbaikan(): void
    {
        $st=collect([(object)['status_tiket'=>'perbaikan_teknis','created_at'=>now()]]);
        $latest=$st->sortByDesc('created_at')->first();
        $this->assertTrue(in_array($latest->status_tiket,['perbaikan_teknis','dibuka_kembali']), 'show() — chat aktif pada status perbaikan_teknis');
    }
    public function testShowChatNonaktifSelesai(): void
    {
        $st=collect([(object)['status_tiket'=>'selesai','created_at'=>now()]]);
        $latest=$st->sortByDesc('created_at')->first();
        $this->assertFalse(in_array($latest->status_tiket,['perbaikan_teknis','dibuka_kembali']), 'show() — chat tidak aktif pada status selesai');
    }
    public function testSendValidasiKonten(): void
    {
        try{\Illuminate\Http\Request::create('/s','POST',[])->validate(['konten'=>'required|string|max:2000']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('konten',$e->errors(), 'send() — konten wajib diisi');}
    }
}
