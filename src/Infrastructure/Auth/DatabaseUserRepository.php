<?php
declare(strict_types=1);
// src/Infrastructure/Auth/DatabaseUserRepository.php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\User;
use App\Domain\Auth\UserRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class DatabaseUserRepository implements UserRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password_hash, role, is_active, created_at, updated_at, last_login 
            FROM users WHERE id = ?
        ");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrateUser($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password_hash, role, is_active, created_at, updated_at, last_login 
            FROM users WHERE username = ? AND is_active = TRUE
        ");
        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrateUser($data) : null;
    }

    /**
     * @throws Exception
     */
    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("
            SELECT id, username, email, password_hash, role, is_active, created_at, updated_at, last_login 
            FROM users WHERE email = ? AND is_active = TRUE
        ");
        $stmt->execute([$email]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        return $data ? $this->hydrateUser($data) : null;
    }

    public function save(User $user): void
    {
        if ($user->id === null) {
            $this->insert($user);
        } else {
            $this->update($user);
        }
    }

    public function updateLastLogin(int $userId): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?
        ");
        $stmt->execute([$userId]);
    }

    private function insert(User $user): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active) 
            VALUES (?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user->username,
            $user->email,
            $user->passwordHash,
            $user->role,
            $user->isActive ? 1 : 0
        ]);
    }

    private function update(User $user): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users 
            SET username = ?, email = ?, password_hash = ?, role = ?, is_active = ? 
            WHERE id = ?
        ");

        $stmt->execute([
            $user->username,
            $user->email,
            $user->passwordHash,
            $user->role,
            $user->isActive ? 1 : 0,
            $user->id
        ]);
    }

    /**
     * @throws Exception
     */
    private function hydrateUser(array $data): User
    {
        return new User(
            (int) $data['id'],
            $data['username'],
            $data['email'],
            $data['password_hash'],
            $data['role'],
            (bool) $data['is_active'],
            new DateTimeImmutable($data['created_at']),
            $data['updated_at'] ? new DateTimeImmutable($data['updated_at']) : null,
            $data['last_login'] ? new DateTimeImmutable($data['last_login']) : null
        );
    }
}