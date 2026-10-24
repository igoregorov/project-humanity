<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Application\ContentManager;
use App\Domain\EventsRepositoryInterface;

final class EventsSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly ContentManager $contentManager,
        private readonly EventsRepositoryInterface $eventsRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->eventsRepository->getWidgetData($lang);
        $events = array_values($this->contentManager->getActiveEvents($lang));
        $maxItems = (int) ($this->config['max_items'] ?? 5);

        return [
            'type' => 'events',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'events' => array_slice($events, 0, $maxItems),
            'max_items' => $maxItems,
        ];
    }
}