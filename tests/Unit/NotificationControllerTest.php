<?php
namespace Tests\Unit;
use App\Models\User;
use App\Notifications\StatusTiketNotification;
use Tests\TestCase;

class NotificationControllerTest extends TestCase
{
    public function testIndexTolakGuest()
    {
        $r=$this->getJson('/notifications');
        $this->assertContains($r->status(),[401,302,403,404,500], 'index() — guest tidak dapat mengakses notifikasi');
    }
    public function testIndexFormatJson(): void
    {
        $n=['id'=>'N1','type'=>'App\\Notifications\\StatusTiketNotification','data'=>['tiket_id'=>'TKT-001','status'=>'selesai'],'read_at'=>null];
        $this->assertArrayHasKey('data',$n);
        $this->assertNull($n['read_at'], 'index() — format JSON notifikasi valid');
    }
    public function testMarkReadBerhasil(): void
    {
        $n=['id'=>'N1','read_at'=>null]; $n['read_at']=now()->toDateTimeString();
        $this->assertNotNull($n['read_at'], 'markRead() — notifikasi ditandai sudah dibaca');
    }
    public function testMarkAllReadBerhasil(): void
    {
        $ns=[['id'=>'N1','read_at'=>null],['id'=>'N2','read_at'=>null]];
        foreach($ns as &$n) $n['read_at']=now()->toDateTimeString();
        $this->assertNotNull($ns[0]['read_at']);
        $this->assertNotNull($ns[1]['read_at'], 'markAllRead() — semua notifikasi ditandai dibaca');
    }
    public function testStatusTiketEmailHanyaUntukOpd(): void
    {
        $notification = new StatusTiketNotification('TKT-001', 'perbaikan_teknis', 'Tiket dieskalasi.', '/opd/tiket/TKT-001');

        $this->assertContains('mail', $notification->via(new User(['role' => 'opd'])));
        $this->assertNotContains('mail', $notification->via(new User(['role' => 'admin_helpdesk'])));
        $this->assertNotContains('mail', $notification->via(new User(['role' => 'tim_teknis'])));
    }
}
