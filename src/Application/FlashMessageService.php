<?php

declare(strict_types=1);

namespace App\Application;

final class FlashMessageService
{
    private const SESSION_KEY = 'living_book_flash';

    public function success(string $message): void
    {
        $this->ensureSession();

        $_SESSION[self::SESSION_KEY]['success'] = $message;
    }

    public function error(string $message): void
    {
        $this->ensureSession();

        $_SESSION[self::SESSION_KEY]['errors'][] = $message;
    }

    public function oldInput(string $key, mixed $value): void
    {
        $this->ensureSession();

        $_SESSION[self::SESSION_KEY]['old'][$key] = $value;
    }

    /**
     * @return array{
     *     success: string|null,
     *     errors: string[],
     *     old: array<string, mixed>
     * }
     */
    public function pull(): array
    {
        $this->ensureSession();

        $flash = $_SESSION[self::SESSION_KEY] ?? [];

        unset($_SESSION[self::SESSION_KEY]);

        return [
            'success' => isset($flash['success']) && is_string($flash['success'])
                ? $flash['success']
                : null,
            'errors' => isset($flash['errors']) && is_array($flash['errors'])
                ? array_values(array_filter($flash['errors'], 'is_string'))
                : [],
            'old' => isset($flash['old']) && is_array($flash['old'])
                ? $flash['old']
                : [],
        ];
    }

    private function ensureSession(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            @session_start();
        }

        if (!isset($_SESSION) || !is_array($_SESSION)) {
            $_SESSION = [];
        }
    }
}