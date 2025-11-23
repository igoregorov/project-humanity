<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\EventsRepositoryInterface;
use Exception;
use PDO;

class DatabaseEventsRepository implements EventsRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function findByLang(string $lang): array
    {
        throw new Exception('DatabaseEventsRepository::findByLang() is not implemented yet.');
    }
}