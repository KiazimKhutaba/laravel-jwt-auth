<?php

namespace Devkit2026\JwtAuth\Services;

use Devkit2026\JwtAuth\DTO\LoginDto;
use Devkit2026\JwtAuth\DTO\RegisterDto;
use Devkit2026\JwtAuth\DTO\UserDto;
use Devkit2026\JwtAuth\Events\UserRegistered;
use Devkit2026\JwtAuth\Exceptions\EmailNotVerifiedException;
use Devkit2026\JwtAuth\Exceptions\InvalidCredentialsException;
use Devkit2026\JwtAuth\Exceptions\JwtAuthException;
use Devkit2026\JwtAuth\Repositories\RefreshTokenRepository;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthService
{
    public function __construct(
        protected JwtService $jwtService,
        protected RefreshTokenRepository $refreshTokenRepo
    ) {}

    public function register(RegisterDto $dto): UserDto
    {
        $userModel = config('jwt_auth.user_model');
        
        $user = $userModel::create([
            'email' => $dto->email,
            'password' => Hash::make($dto->password),
        ]);

        event(new UserRegistered($user));

        return UserDto::fromModel($user);
    }

    /**
     * @throws EmailNotVerifiedException
     * @throws InvalidCredentialsException
     */
    public function login(LoginDto $dto): array
    {
        $userModel = config('jwt_auth.user_model');
        $user = $userModel::where('email', $dto->email)->first();

        if (!$user || !Hash::check($dto->password, $user->password)) {
            throw new InvalidCredentialsException();
        }

        // Check if email verification is required and verified
        if (method_exists($user, 'hasVerifiedEmail') && !$user->hasVerifiedEmail()) {
             throw new EmailNotVerifiedException();
        }

        return $this->createTokens($user);
    }

    /**
     * @throws JwtAuthException
     */
    public function refresh(string $refreshToken): array
    {
        $hashedToken = hash_hmac('sha256', $refreshToken, config('jwt_auth.secret'));
        $storedToken = $this->refreshTokenRepo->findByToken($hashedToken);

        if (!$storedToken) {
            throw new JwtAuthException("Invalid refresh token", 'ERR_TOKEN_INVALID', 401);
        }

        if ($storedToken->isRevoked()) {
            throw new JwtAuthException("Refresh token revoked", 'ERR_REFRESH_TOKEN_REVOKED', 401);
        }

        if ($storedToken->isExpired()) {
            throw new JwtAuthException("Refresh token expired", 'ERR_REFRESH_TOKEN_EXPIRED', 401);
        }

        $user = $storedToken->user;
        
        if (!$user) {
             throw new JwtAuthException("User not found", 'ERR_USER_NOT_FOUND', 404);
        }

        // Rotate refresh token if configured
        if (config('jwt_auth.rotate_refresh')) {
            $this->refreshTokenRepo->revoke($storedToken);
        }

        return $this->createTokens($user);
    }

    public function logout(string $refreshToken): void
    {
        $hashedToken = hash_hmac('sha256', $refreshToken, config('jwt_auth.secret'));
        $storedToken = $this->refreshTokenRepo->findByToken($hashedToken);

        if ($storedToken) {
            $this->refreshTokenRepo->revoke($storedToken);
        }
    }

    protected function createTokens($user): array
    {
        $accessToken = $this->jwtService->generateAccessToken($user);
        
        $refreshTokenPlain = Str::random(64);
        $refreshTokenHash = hash_hmac('sha256', $refreshTokenPlain, config('jwt_auth.secret'));

        $this->refreshTokenRepo->create([
            'user_id' => $user->id,
            'token' => $refreshTokenHash,
            'expires_at' => now()->addMinutes(config('jwt_auth.refresh_ttl')),
            'ip' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshTokenPlain,
            'user' => UserDto::fromModel($user),
        ];
    }
}
