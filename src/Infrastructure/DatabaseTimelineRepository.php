<?php

namespace App\Infrastructure;

use Exception;

class DatabaseTimelineRepository
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function findByLang(string $lang): array
    {
        throw new Exception('DatabaseTimelineRepository::findByLang() is not implemented yet.');
    }
}