<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\TimelineRepositoryInterface;

final class DatabaseTimelineRepository extends AbstractDatabaseItemsRepository implements TimelineRepositoryInterface
{
    protected function table(): string
    {
        return 'timeline_events';
    }

    protected function widgetKey(): string
    {
        return 'timeline';
    }

    protected function defaultWidgetData(): array
    {
        return [
            'widget_title' => 'История развития',
            'no_items' => 'Нет данных для отображения',
        ];
    }

    protected function selectColumns(): string
    {
        return 'id, lang, year, title, description, type, sort_order';
    }

    protected function orderBy(): string
    {
        return 'year ASC, sort_order ASC, id ASC';
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'year' => (string) ($row['year'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'type' => (string) ($row['type'] ?? 'milestone'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }
}