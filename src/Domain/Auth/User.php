<?php
declare(strict_types=1);
// src/Domain/Auth/User.php

namespace App\Domain\Auth;

class User
{
    public function __construct(
        public readonly ?int $id,
        public readonly string $username,
        public readonly string $email,
        public readonly string $passwordHash,
        public readonly string $role,
        public readonly bool $isActive,
        public readonly \DateTimeImmutable $createdAt,
        public readonly ?\DateTimeImmutable $updatedAt,
        public readonly ?\DateTimeImmutable $lastLogin
    ) {}

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }
}