<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;

class EmailVerificationPromptControllerTest extends TestCase
{
    public function testInvokePerluAuth()
    {
        $this->get('/verify-email')->assertRedirect('/login');
    }
}
