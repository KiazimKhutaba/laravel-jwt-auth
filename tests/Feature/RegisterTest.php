<?php

namespace Devkit2026\JwtAuth\Tests\Feature;

use Devkit2026\JwtAuth\Mail\VerifyEmailMail;
use Devkit2026\JwtAuth\Tests\TestCase;
use Illuminate\Support\Facades\Mail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Devkit2026\JwtAuth\Tests\User;

class RegisterTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_register_a_user()
    {
        Mail::fake();

        $response = $this->postJson('/api/auth/register', [
            'email' => 'test@example.com',
            'password' => 'Password123!',
            'password_confirmation' => 'Password123!',
        ]);

        $response->assertStatus(201)
            ->assertJsonStructure([
                'message',
                'user' => ['id', 'email']
            ]);

        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
        ]);

        Mail::assertSent(VerifyEmailMail::class, function ($mail) {
            return $mail->hasTo('test@example.com');
        });
    }

    /** @test */
    public function it_validates_registration_data()
    {
        $response = $this->postJson('/api/auth/register', [
            'email' => 'not-an-email',
            'password' => 'short',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);
    }
}
