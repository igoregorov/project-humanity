<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ContentRepositoryInterface;
use Exception;
use PDO;

class DatabaseContentRepository implements ContentRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function get(string $lang, string $key): string|array|null
    {
        throw new Exception('DatabaseContentRepository::get() is not implemented yet.');
    }

    /**
     * @throws Exception
     */
    public function getAll(string $lang): array
    {
        throw new Exception('DatabaseContentRepository::getAll() is not implemented yet.');
    }
}