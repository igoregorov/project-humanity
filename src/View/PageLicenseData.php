<?php
declare(strict_types=1);

namespace App\View;

class PageLicenseData implements TemplateDataInterface
{
    public function __construct(
        public readonly string $title,
        public readonly string $contentHtml,
        public readonly string $doc,
        public readonly string $lang_code
    ) {}
}