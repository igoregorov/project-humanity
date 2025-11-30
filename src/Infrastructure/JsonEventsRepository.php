<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\EventsRepositoryInterface;

class JsonEventsRepository extends JsonItemsRepository implements EventsRepositoryInterface
{
    protected function getFallbackWidgetData(): array
    {
        return [
            'widget_title' => 'Предстоящие события',
            'no_items' => 'Пока нет предстоящих событий'
        ];
    }
}