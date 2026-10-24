<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\TelegramRepositoryInterface;

final class DatabaseTelegramRepository extends AbstractDatabaseItemsRepository implements TelegramRepositoryInterface
{
    protected function table(): string
    {
        return 'telegram_posts';
    }

    protected function widgetKey(): string
    {
        return 'telegram';
    }

    protected function defaultWidgetData(): array
    {
        return [
            'widget_title' => 'Telegram канал',
            'no_items' => 'Нет постов для отображения',
        ];
    }

    protected function selectColumns(): string
    {
        return 'id, lang, text, posted_at, url, sort_order';
    }

    protected function orderBy(): string
    {
        return 'posted_at DESC, sort_order ASC, id DESC';
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'text' => (string) ($row['text'] ?? ''),
            'posted_at' => isset($row['posted_at']) ? (string) $row['posted_at'] : null,
            'url' => isset($row['url']) ? (string) $row['url'] : null,
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }
}