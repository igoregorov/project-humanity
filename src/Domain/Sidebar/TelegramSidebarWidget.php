<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Domain\TelegramRepositoryInterface;

final class TelegramSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly TelegramRepositoryInterface $telegramRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->telegramRepository->getWidgetData($lang);
        $posts = $this->telegramRepository->findByLang($lang);
        $maxItems = (int) ($this->config['max_items'] ?? 3);

        return [
            'type' => 'telegram',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'posts' => array_slice($posts, 0, $maxItems),
            'channel_url' => (string) ($this->config['channel_url'] ?? '#'),
            'max_items' => $maxItems,
        ];
    }
}