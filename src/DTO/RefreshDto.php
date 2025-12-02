<?php

namespace Devkit2026\JwtAuth\DTO;

class RefreshDto
{
    public function __construct(
        public readonly string $refreshToken,
    ) {}
}
