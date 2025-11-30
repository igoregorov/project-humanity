<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Infrastructure\JsonTelegramRepository;

class TelegramSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly JsonTelegramRepository $telegramRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->telegramRepository->getWidgetData($lang);
        $posts = $this->telegramRepository->findByLang($lang);

        return [
            'type' => 'telegram',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'posts' => $posts,
            'channel_url' => $this->config['channel_url'] ?? '#',
            'max_items' => $this->config['max_items'] ?? 3
        ];
    }
}