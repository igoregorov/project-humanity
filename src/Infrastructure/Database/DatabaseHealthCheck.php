<?php
declare(strict_types=1);
// src/Infrastructure/Database/DatabaseHealthCheck.php

namespace App\Infrastructure\Database;

class DatabaseHealthCheck
{
    public function __construct(
        private readonly DatabaseConnectionManager $connectionManager
    ) {}

    public function isHealthy(): bool
    {
        // Если БД отключена в настройках, сразу возвращаем false
        if (!$this->connectionManager->isActive()) {
            return false;
        }

        try {
            return $this->connectionManager->isConnected();
        } catch (\Exception $e) {
            return false;
        }
    }

    public function getLastError(): ?string
    {
        // Если БД отключена в настройках, возвращаем сообщение
        if (!$this->connectionManager->isActive()) {
            return "База данных отключена в настройках (DB_IS_ACTIVE=false)";
        }

        try {
            $this->connectionManager->getConnection();
            return null;
        } catch (\Exception $e) {
            return $e->getMessage();
        }
    }

    public function isActive(): bool
    {
        return $this->connectionManager->isActive();
    }
}