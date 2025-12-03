<?php

namespace Devkit2026\JwtAuth;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Event;
use Devkit2026\JwtAuth\Console\Commands\JwtSecretCommand;
use Devkit2026\JwtAuth\Http\Middleware\JwtAuthenticate;
use Devkit2026\JwtAuth\Services\JwtService;
use Devkit2026\JwtAuth\Services\AuthService;
use Devkit2026\JwtAuth\Repositories\RefreshTokenRepository;
use Devkit2026\JwtAuth\Events\UserRegistered;
use Devkit2026\JwtAuth\Listeners\SendVerificationEmail;

class JwtAuthServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/jwt_auth.php', 'jwt_auth');

        $this->app->singleton(JwtService::class, function ($app) {
            return new JwtService();
        });

        $this->app->singleton(RefreshTokenRepository::class, function ($app) {
            return new RefreshTokenRepository();
        });

        $this->app->singleton(AuthService::class, function ($app) {
            return new AuthService(
                $app->make(JwtService::class),
                $app->make(RefreshTokenRepository::class)
            );
        });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        $this->registerRoutes();
        $this->registerMigrations();
        $this->registerPublishing();
        $this->registerMiddleware();
        $this->registerCommands();
    }

    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                JwtSecretCommand::class,
            ]);
        }
    }

    protected function registerRoutes(): void
    {
        Route::group([
            'prefix' => 'api/auth',
            'middleware' => 'api',
            'namespace' => 'Devkit2026\JwtAuth\Http\Controllers',
        ], function () {
            $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        });
    }

    protected function registerMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
    }

    protected function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/jwt_auth.php' => config_path('jwt_auth.php'),
            ], 'jwt-auth-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'jwt-auth-migrations');
            
            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/jwt-auth'),
            ], 'jwt-auth-views');
        }
    }

    protected function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('jwt.auth', JwtAuthenticate::class);
    }
}
