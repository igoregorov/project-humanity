<?php
declare(strict_types=1);

// src/Application/ContentManager.php

namespace App\Application;

use App\Domain\EventsRepositoryInterface;
use App\Domain\NewsRepositoryInterface;
use DateTimeImmutable;

class ContentManager
{
    public function __construct(
        private readonly EventsRepositoryInterface $eventsRepository,
        private readonly NewsRepositoryInterface $newsRepository
    ) {}

    /**
     * Получает активные события (будущие)
     */
    public function getActiveEvents(string $lang): array
    {
        $events = $this->eventsRepository->findByLang($lang);
        $now = new DateTimeImmutable();

        return array_filter($events, function ($event) use ($now) {
            if (!isset($event['date'])) return false;

            $eventDate = DateTimeImmutable::createFromFormat('Y-m-d', $event['date']);
            return $eventDate && $eventDate >= $now;
        });
    }

    /**
     * Получает архив событий (прошедшие)
     */
    public function getArchivedEvents(string $lang): array
    {
        $events = $this->eventsRepository->findByLang($lang);
        $now = new DateTimeImmutable();

        return array_filter($events, function ($event) use ($now) {
            if (!isset($event['date'])) return false;

            $eventDate = DateTimeImmutable::createFromFormat('Y-m-d', $event['date']);
            return $eventDate && $eventDate < $now;
        });
    }

    /**
     * Получает все новости (включая архив событий)
     */
    public function getAllNews(string $lang): array
    {
        $news = $this->newsRepository->findByLang($lang);
        $archivedEvents = $this->getArchivedEvents($lang);

        // Объединяем новости и архив событий
        $allContent = array_merge($news, $archivedEvents);

        // Сортируем по дате (новые сначала)
        usort($allContent, function ($a, $b) {
            return strtotime($b['date']) <=> strtotime($a['date']);
        });

        return $allContent;
    }

    /**
     * Получает последние новости (ограниченное количество)
     */
    public function getLatestNews(string $lang, int $limit = 5): array
    {
        $allNews = $this->getAllNews($lang);
        return array_slice($allNews, 0, $limit);
    }
}