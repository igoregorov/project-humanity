// src/Infrastructure/Auth/DatabaseUserRepository.php
<?php
declare(strict_types=1);

namespace App\Infrastructure\Auth;

use App\Domain\Auth\User;
use App\Domain\Auth\UserRepositoryInterface;
use DateTimeImmutable;
use Exception;
use PDO;

class DatabaseUserRepository implements UserRepositoryInterface
{
    private const USER_COLUMNS = 'id, username, email, password_hash, role, is_active, created_at, updated_at, last_login, avatar_path';

    public function __construct(private readonly PDO $pdo) {}

    public function findById(int $id): ?User
    {
        $stmt = $this->pdo->prepare("SELECT " . self::USER_COLUMNS . " FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrateUser($data) : null;
    }

    public function findByUsername(string $username): ?User
    {
        $stmt = $this->pdo->prepare("SELECT " . self::USER_COLUMNS . " FROM users WHERE username = ? AND is_active = TRUE");
        $stmt->execute([$username]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);
        return $data ? $this->hydrateUser($data) : null;
    }

    public function findByEmail(string $email): ?User
    {
        $stmt = $this->pdo->prepare("SELECT " . self::USER_COLUMNS . " FROM users WHERE email = ? AND is_active = TRUE");
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
        $stmt = $this->pdo->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
        $stmt->execute([$userId]);
    }

    private function insert(User $user): void
    {
        $stmt = $this->pdo->prepare("
            INSERT INTO users (username, email, password_hash, role, is_active, avatar_path)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute([
            $user->username,
            $user->email,
            $user->passwordHash,
            $user->role,
            $user->isActive ? 1 : 0,
            $user->avatarPath,
        ]);
    }

    private function update(User $user): void
    {
        $stmt = $this->pdo->prepare("
            UPDATE users
            SET username = ?, email = ?, password_hash = ?, role = ?, is_active = ?, avatar_path = ?
            WHERE id = ?
        ");
        $stmt->execute([
            $user->username,
            $user->email,
            $user->passwordHash,
            $user->role,
            $user->isActive ? 1 : 0,
            $user->avatarPath,
            $user->id,
        ]);
    }

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
            isset($data['updated_at']) ? new DateTimeImmutable($data['updated_at']) : null,
            isset($data['last_login']) ? new DateTimeImmutable($data['last_login']) : null,
            $data['avatar_path'] ?? null
        );
    }
}
