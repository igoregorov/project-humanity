<?php
declare(strict_types=1);
// src/Domain/Auth/UserRepositoryInterface.php

namespace App\Domain\Auth;

interface UserRepositoryInterface
{
    public function findById(int $id): ?User;
    public function findByUsername(string $username): ?User;
    public function findByEmail(string $email): ?User;
    public function save(User $user): void;
    public function updateLastLogin(int $userId): void;
}