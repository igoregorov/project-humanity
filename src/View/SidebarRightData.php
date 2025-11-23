<?php
declare(strict_types=1);

// src/View/SidebarRightData.php
namespace App\View;

use App\Application\LocalizedContentService;
use App\View\TemplateDataInterface;
class SidebarRightData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly array $news_items,
        public readonly string $lang_code
    ) {}
}