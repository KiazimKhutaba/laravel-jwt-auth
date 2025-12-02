<?php

namespace Devkit2026\JwtAuth\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RefreshToken extends Model
{
    protected $table = 'jwt_refresh_tokens';

    protected $fillable = [
        'user_id',
        'token',
        'expires_at',
        'revoked_at',
        'ip',
        'user_agent',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        $userModel = config('jwt_auth.user_model');
        return $this->belongsTo($userModel, 'user_id');
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function isRevoked(): bool
    {
        return !is_null($this->revoked_at);
    }
}
