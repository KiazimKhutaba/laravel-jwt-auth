<?php

namespace Devkit2026\JwtAuth\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Str;

class JwtSecretCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'jwt:secret 
                            {--show : Display the key instead of modifying files}
                            {--force : Force the operation to run when in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Set the JWT secret key';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $key = $this->generateRandomKey();

        if ($this->option('show')) {
            $this->line('<comment>'.$key.'</comment>');
            return self::SUCCESS;
        }

        if (!$this->setKeyInEnvironmentFile($key)) {
            return self::FAILURE;
        }

        $this->laravel['config']['jwt_auth.secret'] = $key;

        $this->components->info('JWT secret set successfully.');

        return self::SUCCESS;
    }

    /**
     * Generate a random key for the application.
     */
    protected function generateRandomKey(): string
    {
        return 'base64:'.base64_encode(
            Str::random(32)
        );
    }

    /**
     * Set the application key in the environment file.
     */
    protected function setKeyInEnvironmentFile(string $key): bool
    {
        $currentKey = $this->laravel['config']['jwt_auth.secret'];

        if (strlen($currentKey) !== 0 && (!$this->confirmToProceed())) {
            return false;
        }

        if (!$this->writeNewEnvironmentFileWith($key)) {
            return false;
        }

        return true;
    }

    /**
     * Write a new environment file with the given key.
     */
    protected function writeNewEnvironmentFileWith(string $key): bool
    {
        $envPath = $this->laravel->environmentFilePath();

        if (!file_exists($envPath)) {
            $this->components->error('Environment file not found.');
            return false;
        }

        $content = file_get_contents($envPath);

        $replaced = preg_replace(
            $this->keyReplacementPattern(),
            'JWT_SECRET='.$key,
            $content
        );

        if ($replaced === $content || $replaced === null) {
            $this->components->error('Unable to set JWT secret. No JWT_SECRET variable was found in the .env file.');
            return false;
        }

        file_put_contents($envPath, $replaced);

        return true;
    }

    /**
     * Get a regex pattern that will match env JWT_SECRET with any random key.
     */
    protected function keyReplacementPattern(): string
    {
        $escaped = preg_quote('='.$this->laravel['config']['jwt_auth.secret'], '/');

        return "/^JWT_SECRET{$escaped}/m";
    }

    /**
     * Confirm before proceeding with the action.
     */
    protected function confirmToProceed(): bool
    {
        if ($this->option('force')) {
            return true;
        }

        $this->components->warn('This will invalidate all existing tokens.');

        return $this->confirm('Do you really wish to run this command?');
    }
}
