<?php
declare(strict_types=1);

// src/Http/Controllers/HomeController.php

namespace App\Http\Controllers;

use App\View\MainContentData;
use Throwable;

class HomeController extends AbstractController
{
    /**
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string
    {
        $siteData = $this->container->get('site_data');
        $translator = $siteData['translator'];
        $lang_code = $siteData['lang_code'];

        $mainContentData = new MainContentData(translator: $translator, lang_code: $lang_code);

        return $this->renderPage('main_content.php', $mainContentData, $page, $lang);
    }
}