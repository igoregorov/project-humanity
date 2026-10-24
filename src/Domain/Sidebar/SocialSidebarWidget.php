<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Domain\SocialRepositoryInterface;

final class SocialSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly SocialRepositoryInterface $socialRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->socialRepository->getWidgetData($lang);
        $networks = $this->socialRepository->findByLang($lang);
        $maxItems = (int) ($this->config['max_items'] ?? 5);

        return [
            'type' => 'social',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'networks' => array_slice($networks, 0, $maxItems),
            'max_items' => $maxItems,
        ];
    }
}