<?php

namespace Devkit2026\JwtAuth\Tests\Feature;

use Devkit2026\JwtAuth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Devkit2026\JwtAuth\Tests\User;

class LoginTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a user
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_login_with_valid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'access_token',
                'token_type',
                'expires_in',
                'user' => ['id', 'email']
            ])
            ->assertCookie('refresh_token');
    }

    /** @test */
    public function it_cannot_login_with_invalid_credentials()
    {
        $response = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'WrongPassword',
        ]);

        $response->assertStatus(401)
            ->assertJson(['error' => ['code' => 'ERR_INVALID_CREDENTIALS']]);
    }

    /** @test */
    public function it_cannot_login_if_email_not_verified()
    {
        User::create([
            'email' => 'unverified@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => null,
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'unverified@example.com',
            'password' => 'Password123!',
        ]);

        $response->assertStatus(403)
            ->assertJson(['error' => ['code' => 'ERR_EMAIL_NOT_VERIFIED']]);
    }
}
