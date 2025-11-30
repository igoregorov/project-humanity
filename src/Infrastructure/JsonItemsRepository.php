<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\ItemsRepositoryInterface;

abstract class JsonItemsRepository implements ItemsRepositoryInterface
{
    public function __construct(private readonly string $itemsPath) {}

    public function findByLang(string $lang): array
    {
        $lang = $lang ?? 'ru';
        $file = $this->itemsPath . "/$lang.json";

        if (!file_exists($file)) {
            error_log("Items file not found: $file");
            return [];
        }

        $json = file_get_contents($file);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            error_log("JSON decode error in $file: " . json_last_error_msg());
            return [];
        }

        return $this->normalizeItems($data['items'] ?? []);
    }

    /**
     * Получает метаданные виджета из конфиг-файла
     */
    public function getWidgetData(string $lang): array
    {
        $lang = $lang ?? 'ru';
        $configFile = $this->itemsPath . "/$lang.config.json";

        if (!file_exists($configFile)) {
            return $this->getFallbackWidgetData();
        }

        $json = file_get_contents($configFile);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($data)) {
            return $this->getFallbackWidgetData();
        }

        return [
            'widget_title' => $data['widget_title'] ?? '',
            'no_items' => $data['no_items'] ?? ''
        ];
    }

    /**
     * Нормализует элементы к единой структуре
     */
    protected function normalizeItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'planned_date' => $item['planned_date'] ?? $item['date'] ?? null,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'is_active' => filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
                'item_type' => $item['item_type'] ?? $item['type'] ?? 'default'
            ];
        }, $items);
    }

    /**
     * Абстрактный метод для получения fallback данных (должен быть реализован в потомках)
     */
    abstract protected function getFallbackWidgetData(): array;
}