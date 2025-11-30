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
}