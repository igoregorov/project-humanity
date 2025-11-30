<?php
declare(strict_types=1);

// src/Domain/Sidebar/SidebarManagerInterface.php

namespace App\Domain\Sidebar;

interface SidebarManagerInterface
{
    public function getSidebarContent(string $position, string $page, string $lang): ?array;
    public function hasSidebar(string $position, string $page): bool;
    public function getSidebarConfig(string $position, string $page): ?array;
}