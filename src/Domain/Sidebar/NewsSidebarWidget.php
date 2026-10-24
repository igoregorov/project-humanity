<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Application\ContentManager;
use App\Domain\NewsRepositoryInterface;

final class NewsSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly ContentManager $contentManager,
        private readonly NewsRepositoryInterface $newsRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->newsRepository->getWidgetData($lang);
        $maxItems = (int) ($this->config['max_items'] ?? 3);
        $news = array_values($this->contentManager->getLatestNews($lang, $maxItems));

        return [
            'type' => 'news',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'news' => array_slice($news, 0, $maxItems),
            'max_items' => $maxItems,
        ];
    }
}