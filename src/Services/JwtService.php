<?php

namespace Devkit2026\JwtAuth\Services;

use Devkit2026\JwtAuth\Exceptions\JwtAuthException;
use Devkit2026\JwtAuth\Exceptions\TokenExpiredException;
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
        $header = json_encode(['typ' => 'JWT', 'alg' => $this->algo]);
        
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

        $payloadJson = json_encode($payload);

        $base64UrlHeader = $this->base64UrlEncode($header);
        $base64UrlPayload = $this->base64UrlEncode($payloadJson);

        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, $this->secret, true);
        $base64UrlSignature = $this->base64UrlEncode($signature);

        return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    }

    /**
     * @throws TokenExpiredException
     * @throws JwtAuthException
     */
    public function decode(string $token): array
    {
        $parts = explode('.', $token);
        
        if (count($parts) !== 3) {
            throw new JwtAuthException("Invalid token format", 'ERR_TOKEN_INVALID', 401);
        }

        [$header, $payload, $signature] = $parts;

        $validSignature = hash_hmac('sha256', $header . "." . $payload, $this->secret, true);
        $validSignatureEncoded = $this->base64UrlEncode($validSignature);

        if (!hash_equals($validSignatureEncoded, $signature)) {
            throw new JwtAuthException("Invalid token signature", 'ERR_TOKEN_INVALID', 401);
        }

        $decodedPayload = json_decode($this->base64UrlDecode($payload), true);

        if (isset($decodedPayload['exp']) && $decodedPayload['exp'] < time()) {
            throw new TokenExpiredException("Token has expired", 'ERR_ACCESS_TOKEN_EXPIRED');
        }

        return $decodedPayload;
    }

    protected function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64UrlDecode(string $data): string
    {
        return base64_decode(str_pad(strtr($data, '-_', '+/'), strlen($data) % 4, '='));
    }
}
