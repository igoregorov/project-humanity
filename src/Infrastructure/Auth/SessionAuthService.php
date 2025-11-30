<?php
declare(strict_types=1);
// src/Infrastructure/Auth/SessionAuthService.php

namespace App\Infrastructure\Auth;

use App\Domain\Auth\AuthServiceInterface;
use App\Domain\Auth\User;
use App\Domain\Auth\UserRepositoryInterface;
use Exception;
use RuntimeException;

class SessionAuthService implements AuthServiceInterface
{
    private const SESSION_USER_KEY = 'auth_user_id';
    private const MAX_LOGIN_ATTEMPTS = 5;
    private const LOGIN_TIMEOUT = 900; // 15 minutes

    public function __construct(
        private readonly UserRepositoryInterface $userRepository
    ) {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public function register(string $username, string $email, string $password): User
    {
        // Валидация
        if (strlen($username) < 3) {
            throw new InvalidArgumentException("Имя пользователя должно быть не менее 3 символов");
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new InvalidArgumentException("Некорректный email адрес");
        }

        if (strlen($password) < 8) {
            throw new InvalidArgumentException("Пароль должен быть не менее 8 символов");
        }

        // Проверка уникальности
        if ($this->userRepository->findByUsername($username)) {
            throw new InvalidArgumentException("Пользователь с таким именем уже существует");
        }

        if ($this->userRepository->findByEmail($email)) {
            throw new InvalidArgumentException("Пользователь с таким email уже существует");
        }

        $user = new User(
            null,
            $username,
            $email,
            $this->hashPassword($password),
            'user',
            true,
            new DateTimeImmutable(),
            null,
            null
        );

        $this->userRepository->save($user);
        return $user;
    }

    public function login(string $username, string $password): ?User
    {
        try {
            if (!$this->checkLoginAttempts()) {
                throw new RuntimeException("Слишком много неудачных попыток входа. Попробуйте позже.");
            }

            $user = $this->userRepository->findByUsername($username);

            if ($user && $this->validatePassword($password, $user->passwordHash)) {
                $_SESSION[self::SESSION_USER_KEY] = $user->id;
                $this->userRepository->updateLastLogin($user->id);
                $this->recordLoginAttempt(true);
                return $user;
            }

            $this->recordLoginAttempt(false);
            return null;
        } catch (RuntimeException $e) {
            // Перебрасываем исключения связанные с бизнес-логикой
            throw $e;
        } catch (Exception $e) {
            // Логируем технические ошибки
            error_log("Ошибка при входе пользователя: " . $e->getMessage());
            throw new RuntimeException("Внутренняя ошибка сервера. Попробуйте позже.");
        }
    }

    public function logout(): void
    {
        unset($_SESSION[self::SESSION_USER_KEY]);
        session_destroy();
    }

    public function getCurrentUser(): ?User
    {
        if (!isset($_SESSION[self::SESSION_USER_KEY])) {
            return null;
        }

        return $this->userRepository->findById($_SESSION[self::SESSION_USER_KEY]);
    }

    public function isLoggedIn(): bool
    {
        return $this->getCurrentUser() !== null;
    }

    public function validatePassword(string $password, string $hash): bool
    {
        return password_verify($password, $hash);
    }

    public function hashPassword(string $password): string
    {
        return password_hash($password, PASSWORD_DEFAULT);
    }

    private function checkLoginAttempts(): bool
    {
        // Простая реализация - можно улучшить записью в БД
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'login_attempts_' . md5($ip);

        $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];

        // Сбрасываем счетчик если прошло больше LOGIN_TIMEOUT
        if (time() - $attempts['time'] > self::LOGIN_TIMEOUT) {
            $attempts = ['count' => 0, 'time' => time()];
        }

        return $attempts['count'] < self::MAX_LOGIN_ATTEMPTS;
    }

    private function recordLoginAttempt(bool $success): void
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        $key = 'login_attempts_' . md5($ip);

        $attempts = $_SESSION[$key] ?? ['count' => 0, 'time' => time()];

        if ($success) {
            $attempts = ['count' => 0, 'time' => time()];
        } else {
            $attempts['count']++;
            $attempts['time'] = time();
        }

        $_SESSION[$key] = $attempts;
    }
}