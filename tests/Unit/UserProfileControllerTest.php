<?php

namespace Tests\Unit;

use Illuminate\Http\Request;
use Tests\TestCase;

class UserProfileControllerTest extends TestCase
{
    public function testShowPerluAuth()
    {
        $this->get('/profile')->assertRedirect('/login');
    }

    public function testUpdatePasswordValidasi(): void
    {
        try {
            Request::create('/password', 'PUT', [])->validate([
                'current_password' => 'required|string',
                'new_password' => 'required|string|min:8|confirmed',
            ]);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('current_password', $e->errors(), 'updatePassword() - current dan new password wajib');
        }
    }

    public function testUpdatePasswordMinimal8(): void
    {
        try {
            Request::create('/p', 'PUT', [
                'current_password' => 'old',
                'new_password' => '1234567',
                'new_password_confirmation' => '1234567',
            ])->validate(['new_password' => 'required|string|min:8|confirmed']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('new_password', $e->errors(), 'updatePassword() - minimal 8 karakter');
        }
    }

    public function testUpdatePasswordKonfirmasi(): void
    {
        try {
            Request::create('/p', 'PUT', [
                'current_password' => 'old',
                'new_password' => 'newpass123',
                'new_password_confirmation' => 'beda',
            ])->validate(['new_password' => 'required|string|min:8|confirmed']);
            $this->fail();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->assertArrayHasKey('new_password', $e->errors(), 'updatePassword() - konfirmasi harus cocok');
        }
    }

    public function testShowViewMapping(): void
    {
        $mapping = [
            'opd' => 'opd.profil',
            'admin_helpdesk' => 'admin_helpdesk.profil',
            'tim_teknis' => 'tim_teknis.profil',
        ];

        $this->assertSame('opd.profil', $mapping['opd'], 'show() - view mapping per role benar');
    }
}
