<?php

namespace Devkit2026\JwtAuth\Exceptions;

class InvalidCredentialsException extends JwtAuthException
{
    public function __construct(string $message = "Invalid email or password.")
    {
        parent::__construct($message, 'ERR_INVALID_CREDENTIALS', 401);
    }
}
