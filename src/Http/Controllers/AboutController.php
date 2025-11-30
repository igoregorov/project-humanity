<?php
declare(strict_types=1);

// src/Http/Controllers/AboutController.php

namespace App\Http\Controllers;

use App\View\PageAboutData;
use Throwable;

class AboutController extends AbstractController
{
    /**
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string
    {
        $about_service = $this->container->get('about_service');
        $aboutData = new PageAboutData(translator: $about_service, lang_code: $lang);

        return $this->renderPage('page_about.php', $aboutData, $page, $lang);
    }
}