<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\NewsRepositoryInterface;
use Exception;
use PDO;

class DatabaseNewsRepository implements NewsRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function findByLang(string $lang): array
    {
        throw new Exception('DatabaseNewsRepository::findByLang() is not implemented yet.');
    }
}