<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\EventsRepositoryInterface;

final class DatabaseEventsRepository extends AbstractDatabaseItemsRepository implements EventsRepositoryInterface
{
    protected function table(): string
    {
        return 'events';
    }

    protected function widgetKey(): string
    {
        return 'events';
    }

    protected function defaultWidgetData(): array
    {
        return [
            'widget_title' => 'Предстоящие события',
            'no_items' => 'Пока нет предстоящих событий',
        ];
    }

    protected function selectColumns(): string
    {
        return 'id, lang, date, title, description, type, sort_order';
    }

    protected function orderBy(): string
    {
        return 'date ASC, sort_order ASC, id ASC';
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'date' => (string) ($row['date'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'type' => (string) ($row['type'] ?? 'event'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }
}