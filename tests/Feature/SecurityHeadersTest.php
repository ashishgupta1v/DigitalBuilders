<?php

namespace Tests\Feature;

use Tests\TestCase;

class SecurityHeadersTest extends TestCase
{
    public function test_homepage_includes_required_security_headers(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertHeader('X-Frame-Options', 'DENY');
        $response->assertHeader('X-Content-Type-Options', 'nosniff');
        $response->assertHeader('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->assertHeader('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        $this->assertTrue($response->headers->has('Content-Security-Policy'));
        $this->assertFalse($response->headers->has('X-Powered-By'));
    }

    public function test_public_registration_is_disabled(): void
    {
        $response = $this->get('/register');
        $response->assertStatus(404);
    }

    public function test_auth_and_login_routes_are_disabled(): void
    {
        $this->get('/login')->assertStatus(404);
        $this->post('/login', [])->assertStatus(404);
        $this->get('/forgot-password')->assertStatus(404);
        $this->get('/dashboard')->assertStatus(404);
        $this->get('/profile')->assertStatus(404);
    }
}
