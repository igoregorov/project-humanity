<?php
declare(strict_types=1);

namespace App\Domain\Sidebar;

use App\Infrastructure\JsonSocialRepository;

class SocialSidebarWidget extends AbstractSidebarWidget
{
    public function __construct(
        private readonly JsonSocialRepository $socialRepository,
        array $config
    ) {
        parent::__construct($config);
    }

    public function getContent(string $lang, array $context = []): array
    {
        $widgetData = $this->socialRepository->getWidgetData($lang);
        $networks = $this->socialRepository->findByLang($lang);

        return [
            'type' => 'social',
            'title' => $widgetData['widget_title'],
            'no_content_message' => $widgetData['no_items'],
            'networks' => $networks,
            'max_items' => $this->config['max_items'] ?? 5
        ];
    }
}