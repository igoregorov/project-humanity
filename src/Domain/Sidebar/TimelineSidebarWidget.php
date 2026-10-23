<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Domain\TimelineRepositoryInterface;

final class TimelineSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly TimelineRepositoryInterface $timelineRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->timelineRepository->getWidgetData($lang);
        $events = $this->timelineRepository->findByLang($lang);
        $maxItems = (int) ($this->config['max_items'] ?? 5);

        return [
            'type' => 'timeline',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'events' => array_slice($events, 0, $maxItems),
            'max_items' => $maxItems,
        ];
    }
}