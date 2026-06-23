<?php

namespace Tests\Unit\Auth;

use Tests\TestCase;

class EmailVerificationNotificationControllerTest extends TestCase
{
    public function testStorePerluAuth()
    {
        $this->post('/email/verification-notification')->assertRedirect('/login');
    }
}
