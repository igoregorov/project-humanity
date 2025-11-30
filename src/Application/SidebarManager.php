<?php
declare(strict_types=1);

// src/Application/SidebarManager.php

namespace App\Application;

use App\Domain\Sidebar\SidebarManagerInterface;
use App\Domain\Sidebar\WidgetFactoryInterface;

class SidebarManager implements SidebarManagerInterface
{
    public function __construct(
        private readonly array $sidebarConfig,
        private readonly WidgetFactoryInterface $widgetFactory
    ) {}

    public function getSidebarContent(string $position, string $page, string $lang): ?array
    {
        $config = $this->getSidebarConfig($position, $page);

        if (!$config || $config['widget'] === 'off') {
            return null;
        }

        $widget = $this->widgetFactory->create($config['widget'], $config);
        return $widget ? $widget->getContent($lang, $config) : null;
    }

    public function hasSidebar(string $position, string $page): bool
    {
        $config = $this->getSidebarConfig($position, $page);
        return $config && $config['widget'] !== 'off';
    }

    public function getSidebarConfig(string $position, string $page): ?array
    {
        return $this->sidebarConfig[$position][$page] ?? null;
    }
}