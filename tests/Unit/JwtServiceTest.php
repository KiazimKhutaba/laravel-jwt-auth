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
}
