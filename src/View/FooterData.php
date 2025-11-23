<?php
declare(strict_types=1);
// src/View/FooterData.php

namespace App\View;

use App\Application\LocalizedContentService;
use App\View\TemplateDataInterface;
class FooterData implements TemplateDataInterface
{
    public function __construct(
        public readonly string $site_title,
        public readonly LocalizedContentService $translator,
        public readonly string $lang_code
    ) {}
}