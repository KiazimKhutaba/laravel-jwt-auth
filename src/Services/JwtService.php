<?php

namespace Devkit2026\JwtAuth\Services;

use Devkit2026\JwtAuth\Exceptions\JwtAuthException;
use Devkit2026\JwtAuth\Exceptions\TokenExpiredException;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Firebase\JWT\ExpiredException;
use Firebase\JWT\SignatureInvalidException;
use Firebase\JWT\BeforeValidException;
use Exception;
use Illuminate\Support\Str;

class JwtService
{
    protected string $secret;
    protected string $algo;
    protected int $accessTtl;

    public function __construct()
    {
        $this->secret = config('jwt_auth.secret');
        $this->algo = config('jwt_auth.algo', 'HS256');
        $this->accessTtl = config('jwt_auth.access_ttl', 60);
    }

    public function generateAccessToken($user): string
    {
        $now = time();
        $payload = array_merge(config('jwt_auth.default_claims', []), [
            'iat' => $now,
            'exp' => $now + ($this->accessTtl * 60),
            'jti' => (string) Str::uuid(),
            'user_id' => $user->id,
        ]);

        // Add additional fields
        foreach (config('jwt_auth.payload_fields', []) as $field) {
            if (isset($user->$field)) {
                $payload[$field] = $user->$field;
            }
        }

        return JWT::encode($payload, $this->secret, $this->algo);
    }

    /**
     * @throws TokenExpiredException
     * @throws JwtAuthException
     */
    public function decode(string $token): array
    {
        try {
            $decoded = JWT::decode($token, new Key($this->secret, $this->algo));
            
            // Convert stdClass to array
            return (array) $decoded;
        } catch (ExpiredException) {
            throw new TokenExpiredException("Token has expired", 'ERR_ACCESS_TOKEN_EXPIRED');
        } catch (SignatureInvalidException) {
            throw new JwtAuthException("Invalid token signature", 'ERR_TOKEN_INVALID', 401);
        } catch (BeforeValidException) {
            throw new JwtAuthException("Token not yet valid", 'ERR_TOKEN_INVALID', 401);
        } catch (Exception) {
            throw new JwtAuthException("Invalid token format", 'ERR_TOKEN_INVALID', 401);
        }
    }
}
