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

            $year = trim((string) ($item['year'] ?? ''));

            if ($year === '') {
                continue;
            }

            $result[] = [
                'year' => $year,
                'title' => (string) ($item['title'] ?? ''),
                'description' => (string) ($item['description'] ?? ''),
                'type' => (string) ($item['type'] ?? $item['item_type'] ?? 'milestone'),
                'is_active' => filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $result;
    }
}