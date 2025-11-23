<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\EventsRepositoryInterface;
use DateTimeImmutable;

class JsonEventsRepository implements EventsRepositoryInterface
{
    public function __construct(private readonly string $eventsPath) {}

    public function findByLang(string $lang): array
    {
        $lang = in_array($lang, ['ru', 'en'], true) ? $lang : 'ru';
        $file = $this->eventsPath . "/$lang.json";

        if (!file_exists($file)) {
            error_log("Events file not found: $file");
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            error_log("JSON decode error in $file: " . json_last_error_msg());
            return [];
        }

        // Фильтруем только будущие события
        $now = new DateTimeImmutable();
        return array_filter($data, function ($event) use ($now) {
            if (!isset($event['date'])) return false;
            $date = DateTimeImmutable::createFromFormat('Y-m-d', $event['date']);
            return $date && $date >= $now;
        });
    }
}