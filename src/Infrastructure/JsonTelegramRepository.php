<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\TelegramRepositoryInterface;

class JsonTelegramRepository extends JsonItemsRepository implements TelegramRepositoryInterface
{
    protected function getFallbackWidgetData(): array
    {
        return [
            'widget_title' => 'Telegram канал',
            'no_items' => 'Нет постов для отображения'
        ];
    }

    protected function normalizeItems(array $items): array
    {
        $result = [];

        foreach ($items as $item) {
            if (!is_array($item)) {
                continue;
            }

            $text = (string) ($item['text'] ?? $item['title'] ?? '');

            if ($text === '') {
                continue;
            }

            $result[] = [
                'text' => $text,
                'posted_at' => isset($item['posted_at'])
                    ? (string) $item['posted_at']
                    : (isset($item['date']) ? (string) $item['date'] : null),
                'url' => isset($item['url']) ? (string) $item['url'] : null,
                'is_active' => filter_var($item['is_active'] ?? true, FILTER_VALIDATE_BOOLEAN),
            ];
        }

        return $result;
    }
}