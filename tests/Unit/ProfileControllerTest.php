<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Tests\TestCase;

class ProfileControllerTest extends TestCase
{
    public function testEditPerluAuth(): void
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function testEditAksesBerhasil(): void
    {
        $this->actingAs(new \App\Models\User(['id' => 'USR-001', 'email' => 't@t.com', 'role' => 'opd']))
            ->get('/profile')
            ->assertOk();
    }

    public function testUpdateValidasiEmail(): void
    {
        try {
            Request::create('/profile', 'PATCH', [])->validate(['email' => 'required|email']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('email', $e->errors(), 'update() - email wajib diisi');
        }
    }

    public function testDestroyValidasiPassword(): void
    {
        try {
            Request::create('/profile', 'DELETE', [])->validate(['password' => 'required|string']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('password', $e->errors(), 'destroy() - password wajib diisi');
        }
    }
}
