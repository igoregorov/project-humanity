<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Application\ContentManager;
use App\Infrastructure\JsonNewsRepository;

class NewsSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly ContentManager $contentManager,
        private readonly JsonNewsRepository $newsRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->newsRepository->getWidgetData($lang);
        $news = $this->contentManager->getLatestNews($lang, $this->config['max_items'] ?? 3);

        return [
            'type' => 'news',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'news' => $news,
            'max_items' => $this->config['max_items'] ?? 3
        ];
    }
}