<?php

namespace Devkit2026\JwtAuth\Exceptions;

class EmailNotVerifiedException extends JwtAuthException
{
    public function __construct(string $message = "Email is not verified.")
    {
        parent::__construct($message, 'ERR_EMAIL_NOT_VERIFIED', 403);
    }
}
