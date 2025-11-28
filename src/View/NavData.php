<?php
declare(strict_types=1);

namespace App\View;

use App\Application\LocalizedContentService;

class NavData implements TemplateDataInterface
{
    public function __construct(
        public readonly string $current_lang,
        public readonly string $current_page,
        public readonly LocalizedContentService $translator
    ) {}

}