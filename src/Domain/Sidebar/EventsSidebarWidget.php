<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Application\ContentManager;
use App\Infrastructure\JsonEventsRepository;

class EventsSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly ContentManager $contentManager,
        private readonly JsonEventsRepository $eventsRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->eventsRepository->getWidgetData($lang);
        $events = $this->contentManager->getActiveEvents($lang);

        return [
            'type' => 'events',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'events' => $events,
            'max_items' => $this->config['max_items'] ?? 5
        ];
    }
}