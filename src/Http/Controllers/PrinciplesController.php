<?php
declare(strict_types=1);

// src/Http/Controllers/PrinciplesController.php

namespace App\Http\Controllers;

use App\View\PagePrinciplesData;
use Throwable;

class PrinciplesController extends AbstractController
{
    /**
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string
    {
        $principles_service = $this->container->get('principles_service');
        $principlesData = new PagePrinciplesData(translator: $principles_service, lang_code: $lang);

        return $this->renderPage('page_principles.php', $principlesData, $page, $lang);
    }
}