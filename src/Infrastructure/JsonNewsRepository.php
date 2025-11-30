<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\NewsRepositoryInterface;

class JsonNewsRepository implements NewsRepositoryInterface
{
    public function __construct(private readonly string $newsPath) {}

    public function findByLang(string $lang): array
    {
        $lang = in_array($lang, ['ru', 'en']) ? $lang : 'ru';
        $file = $this->newsPath . "/$lang.json";

        if (!file_exists($file)) {
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return [];
        }

        return $data['news'] ?? [];
    }

    /**
     * Получает метаданные виджета новостей
     */
    public function getWidgetData(string $lang): array
    {
        $lang = in_array($lang, ['ru', 'en'], true) ? $lang : 'ru';
        $file = $this->newsPath . "/$lang.json";

        if (!file_exists($file)) {
            return [
                'widget_title' => 'Последние новости',
                'no_news' => 'Пока нет новостей'
            ];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        return [
            'widget_title' => $data['widget_title'] ?? 'Последние новости',
            'no_news' => $data['no_news'] ?? 'Пока нет новостей'
        ];
    }
}