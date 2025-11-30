<?php
declare(strict_types=1);
// src/Infrastructure/Database/DatabaseConnectionManager.php

namespace App\Infrastructure\Database;

use PDO;
use RuntimeException;

class DatabaseConnectionManager
{
    private ?PDO $connection = null;
    private array $config;
    private bool $isActive;

    public function __construct(array $databaseConfig)
    {
        $this->config = $databaseConfig;
        $this->isActive = $databaseConfig['is_active'] ?? true;
    }

    public function getConnection(): PDO
    {
        if (!$this->isActive) {
            throw new RuntimeException("База данных отключена в настройках");
        }

        if ($this->connection === null) {
            $this->connect();
        }

        return $this->connection;
    }

    public function isConnected(): bool
    {
        // Если БД отключена в настройках, сразу возвращаем false
        if (!$this->isActive) {
            return false;
        }

        try {
            if ($this->connection === null) {
                return false;
            }

            // Простая проверка соединения
            $this->connection->query('SELECT 1');
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function isActive(): bool
    {
        return $this->isActive;
    }

    public function disconnect(): void
    {
        $this->connection = null;
    }

    private function connect(): void
    {
        // Дополнительная проверка на случай прямого вызова connect()
        if (!$this->isActive) {
            throw new RuntimeException("База данных отключена в настройках");
        }

        $dsn = "mysql:host={$this->config['host']};port={$this->config['port']};dbname={$this->config['dbname']};charset=utf8mb4";

        try {
            $this->connection = new PDO($dsn, $this->config['user'], $this->config['password'], [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                PDO::ATTR_PERSISTENT => false,
                PDO::ATTR_TIMEOUT => 5, // Таймаут 5 секунд
            ]);
        } catch (\Exception $e) {
            throw new RuntimeException(
                "Не удалось подключиться к базе данных: " . $e->getMessage(),
                0,
                $e
            );
        }
    }
}