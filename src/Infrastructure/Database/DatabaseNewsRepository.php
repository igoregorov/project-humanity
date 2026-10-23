<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\NewsRepositoryInterface;

final class DatabaseNewsRepository extends AbstractDatabaseItemsRepository implements NewsRepositoryInterface
{
    protected function table(): string
    {
        return 'news';
    }

    protected function widgetKey(): string
    {
        return 'news';
    }

    protected function defaultWidgetData(): array
    {
        return [
            'widget_title' => 'Последние новости',
            'no_items' => 'Пока нет новостей',
        ];
    }

    protected function selectColumns(): string
    {
        return 'id, lang, date, title, description, type, sort_order';
    }

    protected function orderBy(): string
    {
        return 'date DESC, sort_order ASC, id DESC';
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'date' => (string) ($row['date'] ?? ''),
            'title' => (string) ($row['title'] ?? ''),
            'description' => (string) ($row['description'] ?? ''),
            'type' => (string) ($row['type'] ?? 'news'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }
}