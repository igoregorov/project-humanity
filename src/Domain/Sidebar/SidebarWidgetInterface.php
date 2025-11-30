<?php
declare(strict_types=1);

// src/Domain/Sidebar/SidebarWidgetInterface.php

namespace App\Domain\Sidebar;

interface SidebarWidgetInterface
{
    public function getContent(string $lang, array $context = []): array;
}