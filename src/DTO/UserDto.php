<?php

namespace Devkit2026\JwtAuth\DTO;

class UserDto
{
    public function __construct(
        public readonly int|string $id,
        public readonly string $email,
        public readonly ?string $role = null,
        public readonly array $additionalData = [],
    ) {}

    public static function fromModel($user): self
    {
        return new self(
            id: $user->id,
            email: $user->email,
            role: $user->role ?? null, // Assuming 'role' attribute exists or is null
            additionalData: $user->toArray()
        );
    }
    
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'email' => $this->email,
            'role' => $this->role,
            ...$this->additionalData
        ];
    }
}
