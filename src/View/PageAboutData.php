<?php
declare(strict_types=1);

namespace App\View;

use App\Application\LocalizedContentService;

class PageAboutData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly string $lang_code
    ) {}
}