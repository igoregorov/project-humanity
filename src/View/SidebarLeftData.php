<?php
declare(strict_types=1);

// src/View/SidebarLeftData.php
namespace App\View;

use App\Application\LocalizedContentService;
use App\View\TemplateDataInterface;
class SidebarLeftData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly array $upcoming_events,
        public readonly string $lang_code
    ) {}
}