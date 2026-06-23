<?php
namespace Tests\Unit\Auth;
use Illuminate\Http\Request;
use Tests\TestCase;
class PasswordControllerTest extends TestCase
{
    public function testUpdateValidasi(): void
    {
        try{Request::create('/password','PUT',[])->validate(['current_password'=>'required','password'=>'required|string|confirmed']);$this->fail();}
        catch(\Illuminate\Validation\ValidationException $e){$this->assertArrayHasKey('current_password',$e->errors(), 'update() — current dan new password wajib');}
    }
}
