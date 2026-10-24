<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ContentRepositoryInterface;
use App\Domain\EventsRepositoryInterface;
use App\Domain\NewsRepositoryInterface;
use App\Domain\SocialRepositoryInterface;
use App\Domain\TelegramRepositoryInterface;
use App\Domain\TimelineRepositoryInterface;
use App\Infrastructure\Database\DatabaseContentRepository;
use App\Infrastructure\Database\DatabaseEventsRepository;
use App\Infrastructure\Database\DatabaseNewsRepository;
use App\Infrastructure\Database\DatabaseSocialRepository;
use App\Infrastructure\Database\DatabaseTelegramRepository;
use App\Infrastructure\Database\DatabaseTimelineRepository;
use InvalidArgumentException;
use PDO;

class RepositoryFactory
{
    public static function createContentRepository(
        string $driver,
        string $localesPath,
        ?PDO $pdo = null
    ): ContentRepositoryInterface {
        return match ($driver) {
            'json' => new JsonContentRepository($localesPath),
            'database' => new DatabaseContentRepository(
                self::requirePdo($pdo),
                self::sectionFromPath($localesPath)
            ),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    public static function createEventsRepository(
        string $driver,
        string $eventsPath,
        ?PDO $pdo = null
    ): EventsRepositoryInterface {
        return match ($driver) {
            'json' => new JsonEventsRepository($eventsPath),
            'database' => new DatabaseEventsRepository(self::requirePdo($pdo)),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    public static function createNewsRepository(
        string $driver,
        string $newsPath,
        ?PDO $pdo = null
    ): NewsRepositoryInterface {
        return match ($driver) {
            'json' => new JsonNewsRepository($newsPath),
            'database' => new DatabaseNewsRepository(self::requirePdo($pdo)),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    public static function createTimelineRepository(
        string $driver,
        string $timelinePath,
        ?PDO $pdo = null
    ): TimelineRepositoryInterface {
        return match ($driver) {
            'json' => new JsonTimelineRepository($timelinePath),
            'database' => new DatabaseTimelineRepository(self::requirePdo($pdo)),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    public static function createSocialRepository(
        string $driver,
        string $socialPath,
        ?PDO $pdo = null
    ): SocialRepositoryInterface {
        return match ($driver) {
            'json' => new JsonSocialRepository($socialPath),
            'database' => new DatabaseSocialRepository(self::requirePdo($pdo)),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    public static function createTelegramRepository(
        string $driver,
        string $telegramPath,
        ?PDO $pdo = null
    ): TelegramRepositoryInterface {
        return match ($driver) {
            'json' => new JsonTelegramRepository($telegramPath),
            'database' => new DatabaseTelegramRepository(self::requirePdo($pdo)),
            default => throw new InvalidArgumentException("Неизвестный драйвер хранилища: $driver"),
        };
    }

    private static function requirePdo(?PDO $pdo): PDO
    {
        if ($pdo === null) {
            throw new InvalidArgumentException('Для драйвера database требуется PDO-соединение.');
        }

        return $pdo;
    }

    private static function sectionFromPath(string $path): string
    {
        $normalized = rtrim(str_replace('\\', '/', $path), '/');
        $base = basename($normalized);

        return $base !== '' ? $base : 'main';
    }
}