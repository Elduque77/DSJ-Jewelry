<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationRoutesTest extends TestCase
{
    use RefreshDatabase;

    public function test_cliente_login_is_accessible(): void
    {
        $this->get('/login')->assertOk();
    }

    public function test_admin_login_is_accessible(): void
    {
        $this->get('/admin/login')->assertOk();
    }

    public function test_guest_is_redirected_to_admin_login(): void
    {
        $this->get('/admin')->assertRedirect('/admin/login');
    }
}
