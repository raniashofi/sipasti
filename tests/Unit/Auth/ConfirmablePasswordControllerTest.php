<?php

namespace Tests\Unit\Auth;

use Illuminate\Http\Request;
use Tests\TestCase;

class ConfirmablePasswordControllerTest extends TestCase
{
    public function testShowPerluAuth()
    {
        $this->get('/confirm-password')->assertRedirect('/login');
    }

    public function testStoreValidasiPassword(): void
    {
        try {
            Request::create('/confirm-password', 'POST', [])->validate(['password' => 'required|string']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors(), 'store() - password wajib diisi');
        }
    }
}
