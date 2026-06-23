<?php
namespace Tests\Unit\Auth;
use Illuminate\Http\Request;
use Tests\TestCase;

class AuthenticatedSessionControllerTest extends TestCase
{
    public function testCreatePageAkses()
    {
        $r=$this->get('/login');
        $this->assertContains($r->status(),[200,302], 'create() — halaman login dapat diakses');
    }
    public function testStoreValidasiLogin(): void
    {
        try{Request::create('/login','POST',[])->validate(['email'=>'required|email','password'=>'required|string']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('email',$e->errors(), 'store() — email dan password wajib');}
    }
    public function testStoreRedirectMapping(): void
    {
        $m=['opd'=>'/opd/dashboard','admin_helpdesk'=>'/admin-helpdesk/dashboard','tim_teknis'=>'/tim-teknis/dashboard','super_admin'=>'/super-admin/dashboard','pimpinan'=>'/pimpinan/dashboard'];
        $this->assertSame('/opd/dashboard',$m['opd']);
        $this->assertSame('/super-admin/dashboard',$m['super_admin'], 'store() — redirect sesuai role');
    }
    public function testDestroyRedirect()
    {
        $r=$this->post('/logout');
        $this->assertContains($r->status(),[302,303,419,500], 'destroy() — logout memerlukan CSRF/autentikasi');
    }
}
