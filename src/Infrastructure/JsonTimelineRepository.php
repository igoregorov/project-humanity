<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\TimelineRepositoryInterface;

class JsonTimelineRepository extends JsonItemsRepository implements TimelineRepositoryInterface
{
    protected function getFallbackWidgetData(): array
    {
        return [
            'widget_title' => 'История развития',
            'no_items' => 'Нет данных для отображения'
        ];
    }

    /**
     * Переопределяем нормализацию для таймлайна
     */
    protected function normalizeItems(array $items): array
    {
        return array_map(function ($item) {
            return [
                'planned_date' => $item['year'] ?? $item['planned_date'] ?? null,
                'title' => $item['title'] ?? '',
                'description' => $item['description'] ?? '',
                'is_active' => true, // Для таймлайна всегда активно
                'item_type' => $item['item_type'] ?? $item['type'] ?? 'milestone'
            ];
        }, $items);
    }
}