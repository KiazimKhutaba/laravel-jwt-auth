<?php

namespace Devkit2026\JwtAuth\Exceptions;

class TokenExpiredException extends JwtAuthException
{
    public function __construct(string $message = "Token has expired.", string $errorCode = 'ERR_TOKEN_EXPIRED')
    {
        parent::__construct($message, $errorCode, 401);
    }
}
