<?php
declare(strict_types=1);
// src/Domain/Auth/AuthServiceInterface.php

namespace App\Domain\Auth;

interface AuthServiceInterface
{
    public function register(string $username, string $email, string $password): User;
    public function login(string $username, string $password): ?User;
    public function logout(): void;
    public function getCurrentUser(): ?User;
    public function isLoggedIn(): bool;
    public function validatePassword(string $password, string $hash): bool;
    public function hashPassword(string $password): string;
}