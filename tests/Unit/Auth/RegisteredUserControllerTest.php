<?php
namespace Tests\Unit\Auth;
use Illuminate\Http\Request;
use Tests\TestCase;
class RegisteredUserControllerTest extends TestCase
{
    public function testCreatePageAkses()
    {
        $this->assertContains($this->get('/register')->status(),[200,302], 'create() — halaman register dapat diakses');
    }
    public function testStoreValidasi(): void
    {
        try{Request::create('/register','POST',[])->validate(['name'=>'required|string|max:255','email'=>'required|email','password'=>'required|string|confirmed']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('name',$e->errors());$this->assertArrayHasKey('email',$e->errors(), 'store() — name, email, password wajib');}
    }
}
