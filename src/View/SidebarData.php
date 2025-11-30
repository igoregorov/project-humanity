<?php
declare(strict_types=1);

// src/View/SidebarData.php

namespace App\View;

use App\View\TemplateDataInterface;

class SidebarData implements TemplateDataInterface
{
    public function __construct(
        public readonly array $content,
        public readonly string $position
    ) {}
}