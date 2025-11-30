<?php
declare(strict_types=1);

// src/Domain/Sidebar/AbstractSidebarWidget.php

namespace App\Domain\Sidebar;

abstract class AbstractSidebarWidget implements SidebarWidgetInterface
{
    public function __construct(
        protected array $config
    ) {}

    abstract public function getContent(string $lang, array $context = []): array;
}