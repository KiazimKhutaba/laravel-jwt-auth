<?php

namespace Devkit2026\JwtAuth\Repositories;

use Devkit2026\JwtAuth\Models\RefreshToken;

class RefreshTokenRepository
{
    public function create(array $attributes): RefreshToken
    {
        return RefreshToken::create($attributes);
    }

    public function findByToken(string $token): ?RefreshToken
    {
        return RefreshToken::where('token', $token)->first();
    }

    public function revoke(RefreshToken $token): bool
    {
        return $token->update(['revoked_at' => now()]);
    }
    
    public function revokeAllForUser($userId): void
    {
        RefreshToken::where('user_id', $userId)
            ->whereNull('revoked_at')
            ->update(['revoked_at' => now()]);
    }
}
