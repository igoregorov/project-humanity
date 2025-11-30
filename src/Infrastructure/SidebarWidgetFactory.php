<?php
declare(strict_types=1);

namespace App\Infrastructure;

use App\Domain\Sidebar\WidgetFactoryInterface;
use App\Domain\Sidebar\SidebarWidgetInterface;
use App\Domain\Sidebar\EventsSidebarWidget;
use App\Domain\Sidebar\NewsSidebarWidget;
use App\Domain\Sidebar\TimelineSidebarWidget;
use App\Domain\Sidebar\SocialSidebarWidget;
use App\Domain\Sidebar\TelegramSidebarWidget;

class SidebarWidgetFactory implements WidgetFactoryInterface
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function create(string $widgetType, array $config): ?SidebarWidgetInterface
    {
        return match ($widgetType) {
            'events' => new EventsSidebarWidget(
                $this->container->get('content_manager'),
                $this->container->get('events_repository'),
                $config
            ),
            'news' => new NewsSidebarWidget(
                $this->container->get('content_manager'),
                $this->container->get('news_repository'),
                $config
            ),
            'timeline' => new TimelineSidebarWidget(
                $this->container->get('timeline_repository'),
                $config
            ),
            'social' => new SocialSidebarWidget(
                $this->container->get('social_repository'),
                $config
            ),
            'telegram' => new TelegramSidebarWidget(
                $this->container->get('telegram_repository'),
                $config
            ),
            default => null
        };
    }
}