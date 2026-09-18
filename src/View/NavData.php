<?php
declare(strict_types=1);
// src/View/NavData.php

namespace App\View;

use App\Application\LocalizedContentService;
use App\Application\NavService;

class NavData implements TemplateDataInterface
{
    public function __construct(
        public readonly string $current_lang,
        public readonly string $current_page,
        public readonly LocalizedContentService $translator,
        public readonly NavService $nav_service
    ) {}
}