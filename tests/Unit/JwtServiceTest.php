<?php

namespace Devkit2026\JwtAuth\Tests\Unit;

use Devkit2026\JwtAuth\Exceptions\JwtAuthException;
use Devkit2026\JwtAuth\Exceptions\TokenExpiredException;
use Devkit2026\JwtAuth\Services\JwtService;
use Devkit2026\JwtAuth\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Devkit2026\JwtAuth\Tests\User;

class JwtServiceTest extends TestCase
{
    use RefreshDatabase;

    protected JwtService $jwtService;

    protected function setUp(): void
    {
        parent::setUp();
        $this->jwtService = new JwtService();
    }

    /** @test */
    public function it_can_generate_and_decode_token()
    {
        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $this->jwtService->generateAccessToken($user);

        $payload = $this->jwtService->decode($token);

        $this->assertEquals($user->id, $payload['user_id']);
    }

    /** @test */
    public function it_throws_exception_for_expired_token()
    {
        $this->expectException(TokenExpiredException::class);
        $this->expectExceptionMessage('Token has expired');

        // Create a token that's already expired
        config(['jwt_auth.access_ttl' => -1]); // Set to negative to create expired token
        
        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        // Create a new service instance with the updated config
        $jwtService = new JwtService();
        $token = $jwtService->generateAccessToken($user);
        
        // Wait a moment to ensure the token is expired
        sleep(1);
        
        $jwtService->decode($token);
    }

    /** @test */
    public function it_throws_exception_for_invalid_signature()
    {
        $this->expectException(JwtAuthException::class);
        $this->expectExceptionMessage('Invalid token signature');

        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $this->jwtService->generateAccessToken($user);
        
        // Tamper with the token
        $parts = explode('.', $token);
        $parts[2] = 'tampered_signature';
        $tamperedToken = implode('.', $parts);

        $this->jwtService->decode($tamperedToken);
    }

    /** @test */
    public function it_throws_exception_for_malformed_token()
    {
        $this->expectException(JwtAuthException::class);
        $this->expectExceptionMessage('Invalid token format');

        $this->jwtService->decode('invalid.token');
    }

    /** @test */
    public function it_includes_configured_payload_fields()
    {
        config(['jwt_auth.payload_fields' => ['email']]);

        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $this->jwtService->generateAccessToken($user);
        $payload = $this->jwtService->decode($token);

        $this->assertEquals($user->email, $payload['email']);
    }

    /** @test */
    public function it_includes_default_claims()
    {
        config(['jwt_auth.default_claims' => [
            'iss' => 'http://example.com',
            'aud' => 'http://example.com',
        ]]);

        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $this->jwtService->generateAccessToken($user);
        $payload = $this->jwtService->decode($token);

        $this->assertEquals('http://example.com', $payload['iss']);
        $this->assertEquals('http://example.com', $payload['aud']);
    }

    /** @test */
    public function it_includes_standard_jwt_claims()
    {
        $user = User::create([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $token = $this->jwtService->generateAccessToken($user);
        $payload = $this->jwtService->decode($token);

        $this->assertArrayHasKey('iat', $payload);
        $this->assertArrayHasKey('exp', $payload);
        $this->assertArrayHasKey('jti', $payload);
        $this->assertIsInt($payload['iat']);
        $this->assertIsInt($payload['exp']);
        $this->assertIsString($payload['jti']);
    }
}
