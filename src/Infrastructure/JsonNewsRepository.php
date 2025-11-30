<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\NewsRepositoryInterface;

class JsonNewsRepository extends JsonItemsRepository implements NewsRepositoryInterface
{
    protected function getFallbackWidgetData(): array
    {
        return [
            'widget_title' => 'Последние новости',
            'no_items' => 'Пока нет новостей'
        ];
    }
}