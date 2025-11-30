<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\EventsRepositoryInterface;
use App\Domain\SocialRepositoryInterface;
use Exception;
use PDO;

class DatabaseSocialRepository implements SocialRepositoryInterface
{
    public function __construct(private readonly PDO $pdo) {}

    /**
     * @throws Exception
     */
    public function findByLang(string $lang): array
    {
        throw new Exception('DatabaseSocialRepository::findByLang() is not implemented yet.');
    }

    public function getWidgetData(string $lang): array
    {
        // TODO: Implement getWidgetData() method.
    }
}