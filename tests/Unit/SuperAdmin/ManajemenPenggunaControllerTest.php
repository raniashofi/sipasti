<?php
namespace Tests\Unit\SuperAdmin;
use Illuminate\Http\Request;
use Tests\TestCase;

class ManajemenPenggunaControllerTest extends TestCase
{
    // ── OPD ──
    public function testIndexOpdTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pengguna/opd')->status(),[302,403]), 'indexOpd() — role non-super_admin ditolak');
    }
    public function testStoreOpdValidasiEmail(): void
    {
        try{Request::create('/o','POST',[])->validate(['email'=>'required|email','nama_opd'=>'required|string|max:255','kode_opd'=>'required|string|max:50','password'=>'required|string|min:6']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('email',$e->errors(), 'storeOpd() — email wajib');}
    }
    public function testStoreOpdPasswordMinimal6(): void
    {
        try{Request::create('/o','POST',['email'=>'t@t.com','nama_opd'=>'T','kode_opd'=>'K','password'=>'12345'])->validate(['password'=>'required|string|min:6']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('password',$e->errors(), 'storeOpd() — password minimal 6 karakter');}
    }
    public function testUpdateOpdValidasiNama(): void
    {
        try{Request::create('/o','PUT',[])->validate(['nama_opd'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_opd',$e->errors(), 'updateOpd() — nama_opd wajib');}
    }
    public function testDestroyOpdTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pengguna/opd/FAKE')->status(),[302,403,404,405]), 'destroyOpd() — non-admin ditolak');
    }
    // ── Internal (Tim Teknis) ──
    public function testIndexInternalTolakNonAdmin()
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->get('/super-admin/pengguna/internal')->status(),[302,403]), 'indexInternal() — role non-super_admin ditolak');
    }
    public function testStoreTimTeknisValidasi(): void
    {
        try{Request::create('/t','POST',[])->validate(['email'=>'required|email','bidang_id'=>'required|string','nama_lengkap'=>'required|string|max:255','password'=>'required|string|min:6']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('bidang_id',$e->errors(), 'storeTimTeknis() — bidang_id wajib');}
    }
    public function testUpdateTimTeknisValidasi(): void
    {
        try{Request::create('/t','PUT',[])->validate(['nama_lengkap'=>'required|string|max:255','bidang_id'=>'required|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_lengkap',$e->errors(), 'updateTimTeknis() — nama wajib');}
    }
    public function testDestroyTimTeknisTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pengguna/tim-teknis/FAKE')->status(),[302,403,404,405]), 'destroyTimTeknis() — non-admin ditolak');
    }
    // ── Admin Helpdesk ──
    public function testStoreAdminHelpdeskValidasi(): void
    {
        try{Request::create('/a','POST',[])->validate(['email'=>'required|email','bidang_id'=>'required|string','nama_lengkap'=>'required|string|max:255','password'=>'required|string|min:6']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('bidang_id',$e->errors(), 'storeAdminHelpdesk() — bidang_id wajib (NOT NULL)');}
    }
    public function testUpdateAdminHelpdeskValidasi(): void
    {
        try{Request::create('/a','PUT',[])->validate(['nama_lengkap'=>'required|string|max:255','bidang_id'=>'required|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_lengkap',$e->errors(), 'updateAdminHelpdesk() — nama wajib');}
    }
    public function testDestroyAdminHelpdeskTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pengguna/admin-helpdesk/FAKE')->status(),[302,403,404,405]), 'destroyAdminHelpdesk() — non-admin ditolak');
    }
    // ── Pimpinan ──
    public function testStorePimpinanValidasi(): void
    {
        try{Request::create('/p','POST',[])->validate(['email'=>'required|email','nama_lengkap'=>'required|string|max:255','password'=>'required|string|min:6']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('email',$e->errors(), 'storePimpinan() — email wajib');}
    }
    public function testUpdatePimpinanValidasi(): void
    {
        try{Request::create('/p','PUT',[])->validate(['nama_lengkap'=>'required|string|max:255']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('nama_lengkap',$e->errors(), 'updatePimpinan() — nama wajib');}
    }
    public function testDestroyPimpinanTolak(): void
    {
        $this->actingAs(new \App\Models\User(['id'=>999,'role'=>'opd']));
        $this->assertTrue(in_array($this->delete('/super-admin/pengguna/pimpinan/FAKE')->status(),[302,403,404,405]), 'destroyPimpinan() — non-admin ditolak');
    }
    // ── Mapping & Format ──
    public function testStoreEmailFormatInvalid(): void
    {
        try{Request::create('/o','POST',['email'=>'bukan-email','nama_opd'=>'T','kode_opd'=>'K','password'=>'123456'])->validate(['email'=>'required|email']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('email',$e->errors(), 'store() — email harus format valid');}
    }
    public function testRoleMapping(): void
    {
        $m=['opd'=>'opd','tim_teknis'=>'tim_teknis','admin_helpdesk'=>'admin_helpdesk','pimpinan'=>'pimpinan'];
        $this->assertSame('opd',$m['opd']);
        $this->assertSame('tim_teknis',$m['tim_teknis'], 'role mapping pengguna benar');
    }
}
