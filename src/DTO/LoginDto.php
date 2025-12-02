<?php

namespace Devkit2026\JwtAuth\DTO;

class LoginDto
{
    public function __construct(
        public readonly string $email,
        public readonly string $password,
    ) {}
}
