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
        $result = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $result[] = [
                'year' => (string) ($item['year'] ?? $item['planned_date'] ?? ''),
                'title' => (string) ($item['title'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
                'type' => (string) ($item['type'] ?? $item['item_type'] ?? 'milestone'),
                'is_active' => true,
            ];
        }

        return $result;
    }
}