<?php

namespace Devkit2026\JwtAuth\Tests\Feature;

use Devkit2026\JwtAuth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Devkit2026\JwtAuth\Tests\User;

class RefreshTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        User::create([
            'email' => 'test@example.com',
            'password' => Hash::make('Password123!'),
            'email_verified_at' => now(),
        ]);
    }

    /** @test */
    public function it_can_refresh_token_with_cookie_method()
    {
        config(['jwt_auth.refresh_token_method' => 'cookie']);

        // Login to get refresh token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $refreshToken = $loginResponse->getCookie('refresh_token', false)->getValue();

        // Refresh - use withUnencryptedCookie since session encryption is disabled in tests
        $response = $this->withUnencryptedCookie('refresh_token', $refreshToken)
            ->postJson('/api/auth/refresh');

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'user'])
            ->assertCookie('refresh_token');
            
        // Ensure new refresh token is different (rotation)
        $newRefreshToken = $response->getCookie('refresh_token', false)->getValue();
        $this->assertNotEquals($refreshToken, $newRefreshToken);
    }

    /** @test */
    public function it_can_refresh_token_with_body_method()
    {
        config(['jwt_auth.refresh_token_method' => 'body']);

        // Login to get refresh token
        $loginResponse = $this->postJson('/api/auth/login', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
        ]);

        $loginResponse->assertJsonStructure(['refresh_token']);
        $refreshToken = $loginResponse->json('refresh_token');

        // Refresh with token in body
        $response = $this->postJson('/api/auth/refresh', [
            'refresh_token' => $refreshToken
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['access_token', 'refresh_token', 'user']);
            
        // Ensure new refresh token is different (rotation)
        $newRefreshToken = $response->json('refresh_token');
        $this->assertNotEquals($refreshToken, $newRefreshToken);
    }

    /** @test */
    public function it_fails_with_invalid_refresh_token()
    {
        config(['jwt_auth.refresh_token_method' => 'cookie']);

        $response = $this->withUnencryptedCookie('refresh_token', 'invalid-token')
            ->postJson('/api/auth/refresh');

        $response->assertStatus(401);
    }
}
