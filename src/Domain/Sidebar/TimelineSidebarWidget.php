<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Infrastructure\JsonTimelineRepository;

class TimelineSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly JsonTimelineRepository $timelineRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->timelineRepository->getWidgetData($lang);
        $events = $this->timelineRepository->findByLang($lang);

        return [
            'type' => 'timeline',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'events' => $events,
            'max_items' => $this->config['max_items'] ?? 5
        ];
    }
}