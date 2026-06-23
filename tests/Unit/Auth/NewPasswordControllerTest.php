<?php
namespace Tests\Unit\Auth;
use Illuminate\Http\Request;
use Tests\TestCase;
class NewPasswordControllerTest extends TestCase
{
    public function testCreatePageAkses()
    {
        $this->assertContains($this->get('/reset-password/fake-token')->status(),[200,302], 'create() — halaman reset password dapat diakses');
    }
    public function testStoreValidasi(): void
    {
        try{Request::create('/reset-password','POST',[])->validate(['token'=>'required','email'=>'required|email','password'=>'required|string|confirmed']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('token',$e->errors(), 'store() — token, email, password wajib');}
    }
}
