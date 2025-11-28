<?php
declare(strict_types=1);

namespace App\View;

class NavData implements TemplateDataInterface
{
    public function __construct(
        public readonly string $current_lang,
        public readonly string $current_page
    ) {}
}