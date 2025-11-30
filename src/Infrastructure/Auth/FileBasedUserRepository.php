<?php
declare(strict_types=1);
// src/Infrastructure/Auth/FileBasedUserRepository.php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\User;
use App\Domain\Auth\UserRepositoryInterface;

class FileBasedUserRepository implements UserRepositoryInterface
{
    private string $storagePath;
    private array $users = [];

    public function __construct(string $storagePath = __DIR__ . '/../../../storage/users.json')
    {
        $this->storagePath = $storagePath;
        $this->loadUsers();
    }

    public function findById(int $id): ?User
    {
        return $this->users[$id] ?? null;
    }

    public function findByUsername(string $username): ?User
    {
        foreach ($this->users as $user) {
            if ($user->username === $username && $user->isActive) {
                return $user;
            }
        }
        return null;
    }

    public function findByEmail(string $email): ?User
    {
        foreach ($this->users as $user) {
            if ($user->email === $email && $user->isActive) {
                return $user;
            }
        }
        return null;
    }

    public function save(User $user): void
    {
        if ($user->id === null) {
            $user = new User(
                $this->getNextId(),
                $user->username,
                $user->email,
                $user->passwordHash,
                $user->role,
                $user->isActive,
                $user->createdAt,
                new \DateTimeImmutable(),
                $user->lastLogin
            );
        }

        $this->users[$user->id] = $user;
        $this->saveUsers();
    }

    public function updateLastLogin(int $userId): void
    {
        if (isset($this->users[$userId])) {
            $user = $this->users[$userId];
            $this->users[$userId] = new User(
                $user->id,
                $user->username,
                $user->email,
                $user->passwordHash,
                $user->role,
                $user->isActive,
                $user->createdAt,
                $user->updatedAt,
                new \DateTimeImmutable()
            );
            $this->saveUsers();
        }
    }

    private function loadUsers(): void
    {
        if (file_exists($this->storagePath)) {
            $data = json_decode(file_get_contents($this->storagePath), true) ?? [];
            foreach ($data as $userData) {
                $user = new User(
                    $userData['id'],
                    $userData['username'],
                    $userData['email'],
                    $userData['passwordHash'],
                    $userData['role'],
                    $userData['isActive'],
                    new \DateTimeImmutable($userData['createdAt']),
                    $userData['updatedAt'] ? new \DateTimeImmutable($userData['updatedAt']) : null,
                    $userData['lastLogin'] ? new \DateTimeImmutable($userData['lastLogin']) : null
                );
                $this->users[$user->id] = $user;
            }
        }
    }

    private function saveUsers(): void
    {
        $data = [];
        foreach ($this->users as $user) {
            $data[] = [
                'id' => $user->id,
                'username' => $user->username,
                'email' => $user->email,
                'passwordHash' => $user->passwordHash,
                'role' => $user->role,
                'isActive' => $user->isActive,
                'createdAt' => $user->createdAt->format('c'),
                'updatedAt' => $user->updatedAt?->format('c'),
                'lastLogin' => $user->lastLogin?->format('c'),
            ];
        }

        // Создаем директорию если не существует
        $dir = dirname($this->storagePath);
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        file_put_contents($this->storagePath, json_encode($data, JSON_PRETTY_PRINT));
    }

    private function getNextId(): int
    {
        return empty($this->users) ? 1 : max(array_keys($this->users)) + 1;
    }
}