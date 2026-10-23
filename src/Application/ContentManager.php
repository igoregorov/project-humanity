<?php
declare(strict_types=1);

namespace App\Application;

use App\Domain\EventsRepositoryInterface;
use App\Domain\NewsRepositoryInterface;
use DateTimeImmutable;

class ContentManager
{
    public function __construct(
        private readonly EventsRepositoryInterface $eventsRepository,
        private readonly NewsRepositoryInterface $newsRepository
    ) {
    }

    /**
     * Получает активные события (сегодня и будущие).
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function getActiveEvents(string $lang): array
    {
        $events = $this->eventsRepository->findByLang($lang);
        $today = new DateTimeImmutable('today');

        return array_filter($events, static function ($event) use ($today): bool {
            if (!isset($event['date']) || $event['date'] === '') {
                return false;
            }

            $eventDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['date']);

            return $eventDate !== false && $eventDate >= $today;
        });
    }

    /**
     * Получает архив событий (строго прошедшие).
     *
     * @return array<int|string, array<string, mixed>>
     */
    public function getArchivedEvents(string $lang): array
    {
        $events = $this->eventsRepository->findByLang($lang);
        $today = new DateTimeImmutable('today');

        return array_filter($events, static function ($event) use ($today): bool {
            if (!isset($event['date']) || $event['date'] === '') {
                return false;
            }

            $eventDate = DateTimeImmutable::createFromFormat('Y-m-d', (string) $event['date']);

            return $eventDate !== false && $eventDate < $today;
        });
    }

    /**
     * Получает все новости и архив событий, отсортированные по дате.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getAllNews(string $lang): array
    {
        $news = $this->newsRepository->findByLang($lang);
        $archivedEvents = $this->getArchivedEvents($lang);

        $allContent = array_merge($news, array_values($archivedEvents));

        usort($allContent, static function ($a, $b): int {
            $aTime = isset($a['date']) ? strtotime((string) $a['date']) : 0;
            $bTime = isset($b['date']) ? strtotime((string) $b['date']) : 0;

            return $bTime <=> $aTime;
        });

        return $allContent;
    }

    /**
     * Получает последние новости.
     *
     * @return array<int, array<string, mixed>>
     */
    public function getLatestNews(string $lang, int $limit = 5): array
    {
        $allNews = $this->getAllNews($lang);

        return array_slice($allNews, 0, max(0, $limit));
    }
}