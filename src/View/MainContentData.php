<?php
declare(strict_types=1);

// src/View/MainContentData.php
namespace App\View;

use App\Application\LocalizedContentService;
use App\View\TemplateDataInterface;

class MainContentData implements TemplateDataInterface
{
    public function __construct(
        public readonly LocalizedContentService $translator,
        public readonly string $lang_code
    ) {}
}