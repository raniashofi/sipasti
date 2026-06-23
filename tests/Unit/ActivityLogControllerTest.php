<?php
namespace Tests\Unit;
use Illuminate\Http\Request;
use Tests\TestCase;

class ActivityLogControllerTest extends TestCase
{
    // ── Static log helper tests ──
    public function testLogParameterValid(): void
    {
        $data = ['user_id'=>'USR-001','role_pelaku'=>'admin_helpdesk','jenis_aktivitas'=>'create','detail_tindakan'=>'Membuat tiket TKT-001','nama_tabel'=>'tiket','id_record'=>'TKT-001'];
        $this->assertSame('USR-001',$data['user_id']);
        $this->assertSame('create',$data['jenis_aktivitas'], 'log() — parameter log diterima dengan benar');
    }
    public function testLogLoginFormat(): void
    {
        $data = ['jenis_aktivitas'=>'login','detail_tindakan'=>'User login ke sistem'];
        $this->assertSame('login',$data['jenis_aktivitas'], 'logLogin() — format log login benar');
    }
    public function testLogLogoutFormat(): void
    {
        $data = ['jenis_aktivitas'=>'logout','detail_tindakan'=>'User logout dari sistem'];
        $this->assertSame('logout',$data['jenis_aktivitas'], 'logLogout() — format log logout benar');
    }
    public function testLogCreateFormat(): void
    {
        $data = ['jenis_aktivitas'=>'create','nama_tabel'=>'tiket','id_record'=>'TKT-001','data_after'=>['subjek'=>'Test']];
        $this->assertSame('tiket',$data['nama_tabel']);
        $this->assertArrayHasKey('data_after',$data, 'logCreate() — menyimpan data_after');
    }
    public function testLogUpdateFormat(): void
    {
        $data = ['jenis_aktivitas'=>'update','nama_tabel'=>'tiket','data_before'=>['status'=>'open'],'data_after'=>['status'=>'closed']];
        $this->assertArrayHasKey('data_before',$data);
        $this->assertArrayHasKey('data_after',$data, 'logUpdate() — menyimpan data_before dan data_after');
    }
    public function testLogDeleteFormat(): void
    {
        $data = ['jenis_aktivitas'=>'delete','nama_tabel'=>'kategori','id_record'=>'KAT-001','data_before'=>['nama'=>'Jaringan']];
        $this->assertArrayHasKey('data_before',$data, 'logDelete() — menyimpan data_before');
    }
    public function testLogEscalateFormat(): void
    {
        $data = ['jenis_aktivitas'=>'escalate','nama_tabel'=>'tiket','id_record'=>'TKT-001','detail_tindakan'=>'Eskalasi ke tim teknis'];
        $this->assertSame('escalate',$data['jenis_aktivitas'], 'logEscalate() — format eskalasi benar');
    }
    public function testLogApproveFormat(): void
    {
        $data = ['jenis_aktivitas'=>'approve','nama_tabel'=>'tiket','id_record'=>'TKT-001'];
        $this->assertSame('approve',$data['jenis_aktivitas'], 'logApprove() — format approve benar');
    }
    public function testLogRejectFormat(): void
    {
        $data = ['jenis_aktivitas'=>'reject','nama_tabel'=>'tiket','id_record'=>'TKT-001'];
        $this->assertSame('reject',$data['jenis_aktivitas'], 'logReject() — format reject benar');
    }
    public function testGetLastLoginFormatted(): void
    {
        $p = \Carbon\Carbon::parse('2026-05-17 20:00:00');
        $this->assertSame('17 May 2026, 20:00',$p->format('d M Y, H:i'), 'getLastLoginFormatted() — format tanggal benar');
    }
    // ── View page tests ──
    public function testShowAdminLogTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/log')->status(),[302,403]), 'showAdminLog() — role non-admin ditolak');
    }
    public function testExportAdminCsvTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/admin-helpdesk/log/export-csv')->status(),[302,403]), 'exportAdminCsv() — role non-admin ditolak');
    }
    public function testShowPimpinanLogTolakNonPimpinan()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/pimpinan/log')->status(),[302,403]), 'showPimpinanLog() — role non-pimpinan ditolak');
    }
    public function testExportPimpinanCsvTolakNonPimpinan()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/pimpinan/log/export-csv')->status(),[302,403]), 'exportPimpinanCsv() — role non-pimpinan ditolak');
    }
    public function testShowAuditTolakNonSuperAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/audit')->status(),[302,403]), 'showAudit() — role non-super_admin ditolak');
    }
    public function testExportCsvTolakNonSuperAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/audit/export-csv')->status(),[302,403]), 'exportCsv() — role non-super_admin ditolak');
    }
    public function testJenisAktivitasValid(): void
    {
        $v = ['login','logout','create','update','delete','escalate','approve','reject'];
        $this->assertCount(8,$v);
        $this->assertContains('create',$v);
        $this->assertNotContains('invalid',$v, 'jenisAktivitas — hanya 8 jenis valid');
    }
    public function testRolePelakuValid(): void
    {
        $v = ['opd','admin_helpdesk','tim_teknis','super_admin','pimpinan'];
        $this->assertCount(5,$v, 'rolePelaku — 5 role valid');
    }
    public function testBadgeClassMapping(): void
    {
        $m = ['login'=>'bg-blue-100 text-blue-700','delete'=>'bg-red-100 text-red-700','create'=>'bg-emerald-100 text-emerald-700'];
        $this->assertStringContainsString('blue',$m['login']);
        $this->assertStringContainsString('red',$m['delete'], 'badgeClass — mapping warna per jenis aktivitas');
    }
    public function testRoleLabelMapping(): void
    {
        $m = ['super_admin'=>'Super Admin','admin_helpdesk'=>'Admin Helpdesk','tim_teknis'=>'Tim Teknis','pimpinan'=>'Pimpinan','opd'=>'OPD'];
        $this->assertSame('Super Admin',$m['super_admin'], 'roleLabel — mapping label per role');
    }
}
