<?php

namespace Devkit2026\JwtAuth\Tests;

use Devkit2026\JwtAuth\JwtAuthServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

abstract class TestCase extends Orchestra
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->setUpDatabase();
    }

    protected function getPackageProviders($app): array
    {
        return [
            JwtAuthServiceProvider::class,
        ];
    }

    protected function getEnvironmentSetUp($app): void
    {
        $app['config']->set('database.default', 'sqlite');
        $app['config']->set('database.connections.sqlite', [
            'driver'   => 'sqlite',
            'database' => ':memory:',
            'prefix'   => '',
        ]);
        
        $app['config']->set('jwt_auth.secret', 'test_secret_key_at_least_32_chars_long');
        $app['config']->set('jwt_auth.user_model', User::class);
        
        // Disable cookie encryption for testing
        $app['config']->set('session.encrypt', false);
    }
    
    protected function setUpDatabase()
    {
        // Create users table for testing
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name')->nullable();
            $table->string('email')->unique();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password');
            $table->rememberToken();
            $table->timestamps();
        });
    }
}
