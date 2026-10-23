<?php
declare(strict_types=1);

namespace App\Infrastructure\Database;

use App\Domain\SocialRepositoryInterface;

final class DatabaseSocialRepository extends AbstractDatabaseItemsRepository implements SocialRepositoryInterface
{
    protected function table(): string
    {
        return 'social_links';
    }

    protected function widgetKey(): string
    {
        return 'social';
    }

    protected function defaultWidgetData(): array
    {
        return [
            'widget_title' => 'Мы в соцсетях',
            'no_items' => 'Нет доступных соцсетей',
        ];
    }

    protected function selectColumns(): string
    {
        return 'id, lang, code, name, url, sort_order';
    }

    protected function orderBy(): string
    {
        return 'sort_order ASC, id ASC';
    }

    protected function normalizeRow(array $row): array
    {
        return [
            'id' => isset($row['id']) ? (int) $row['id'] : null,
            'code' => (string) ($row['code'] ?? ''),
            'name' => (string) ($row['name'] ?? ''),
            'url' => (string) ($row['url'] ?? '#'),
            'sort_order' => (int) ($row['sort_order'] ?? 0),
        ];
    }
}