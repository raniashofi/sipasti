<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;

class VerifyEmailControllerTest extends TestCase
{
    public function testInvokePerluAuth()
    {
        $this->get('/verify-email/1/hash')->assertRedirect('/login');
    }
}
