<?php
namespace Tests\Unit\Auth;
use Illuminate\Http\Request;
use Tests\TestCase;
class PasswordResetLinkControllerTest extends TestCase
{
    public function testCreatePageAkses()
    {
        $this->assertContains($this->get('/forgot-password')->status(),[200,302], 'create() — halaman forgot password dapat diakses');
    }
    public function testStoreValidasiEmail(): void
    {
        try{Request::create('/forgot-password','POST',[])->validate(['email'=>'required|email']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('email',$e->errors(), 'store() — email wajib diisi');}
    }
}
