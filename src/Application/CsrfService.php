<?php

declare(strict_types=1);

namespace App\Application;

final class CsrfService
{
    private const SESSION_KEY = 'csrf_token';
    private const TTL = 3600;

    public function getToken(bool $forceNew = false): string
    {
        $this->ensureSession();

        if (
            $forceNew
            || !isset($_SESSION[self::SESSION_KEY])
            || !is_array($_SESSION[self::SESSION_KEY])
        ) {
            return $this->regenerate();
        }

        $token = $_SESSION[self::SESSION_KEY];

        if (
            !isset($token['value'], $token['created_at'])
            || !is_string($token['value'])
            || !is_int($token['created_at'])
        ) {
            return $this->regenerate();
        }

        if ($token['created_at'] + self::TTL < time()) {
            return $this->regenerate();
        }

        return $token['value'];
    }

    public function validateToken(string $token): bool
    {
        $this->ensureSession();

        if ($token === '') {
            return false;
        }

        if (
            !isset($_SESSION[self::SESSION_KEY])
            || !is_array($_SESSION[self::SESSION_KEY])
        ) {
            return false;
        }

        $stored = $_SESSION[self::SESSION_KEY];

        if (
            !isset($stored['value'], $stored['created_at'])
            || !is_string($stored['value'])
            || !is_int($stored['created_at'])
        ) {
            return false;
        }

        if ($stored['created_at'] + self::TTL < time()) {
            return false;
        }

        return hash_equals($stored['value'], $token);
    }

    private function regenerate(): string
    {
        $value = bin2hex(random_bytes(32));

        $_SESSION[self::SESSION_KEY] = [
            'value' => $value,
            'created_at' => time(),
        ];

        return $value;
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