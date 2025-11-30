<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ContentRepositoryInterface;
use App\Domain\EventsRepositoryInterface;
use App\Domain\NewsRepositoryInterface;
use App\Domain\TimelineRepositoryInterface;
use App\Domain\SocialRepositoryInterface;
use App\Domain\TelegramRepositoryInterface;
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
            'database' => new DatabaseContentRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }

    public static function createEventsRepository(
        string $driver,
        string $eventsPath,
        ?PDO $pdo = null
    ): EventsRepositoryInterface {
        return match ($driver) {
            'json' => new JsonEventsRepository($eventsPath),
            'database' => new DatabaseEventsRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }

    public static function createNewsRepository(
        string $driver,
        string $newsPath,
        ?PDO $pdo = null
    ): NewsRepositoryInterface {
        return match ($driver) {
            'json' => new JsonNewsRepository($newsPath),
            'database' => new DatabaseNewsRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }

    public static function createTimelineRepository(
        string $driver,
        string $timelinePath,
        ?PDO $pdo = null
    ): TimelineRepositoryInterface {
        return match ($driver) {
            'json' => new JsonTimelineRepository($timelinePath),
            'database' => new DatabaseTimelineRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }

    public static function createSocialRepository(
        string $driver,
        string $socialPath,
        ?PDO $pdo = null
    ): SocialRepositoryInterface {
        return match ($driver) {
            'json' => new JsonSocialRepository($socialPath),
            'database' => new DatabaseSocialRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }

    public static function createTelegramRepository(
        string $driver,
        string $telegramPath,
        ?PDO $pdo = null
    ): TelegramRepositoryInterface {
        return match ($driver) {
            'json' => new JsonTelegramRepository($telegramPath),
            'database' => new DatabaseTelegramRepository($pdo),
            default => throw new \InvalidArgumentException("Неизвестный драйвер хранилища: $driver")
        };
    }
}