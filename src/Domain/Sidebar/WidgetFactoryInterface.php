<?php
declare(strict_types=1);

// src/Domain/Sidebar/WidgetFactoryInterface.php

namespace App\Domain\Sidebar;

interface WidgetFactoryInterface
{
    public function create(string $widgetType, array $config): ?SidebarWidgetInterface;
}