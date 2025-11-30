<?php
declare(strict_types=1);

// src/Application/PageDataService.php

namespace App\Application;

use App\Infrastructure\ContainerInterface;
use App\View\NavData;
use App\View\FooterData;

class PageDataService
{
    public function __construct(
        private readonly ContainerInterface $container
    ) {}

    public function prepareLayoutData(string $page, string $lang): array
    {
        $siteData = $this->container->get('site_data');
        $sidebarManager = $this->container->get('sidebar_manager');

        $translator = $siteData['translator'];
        $lang_code = $siteData['lang_code'];

        $navData = new NavData(
            current_lang: $lang_code,
            current_page: $page,
            translator: $translator
        );

        $footerData = new FooterData(
            site_title: $siteData['site_title'],
            translator: $translator,
            lang_code: $lang_code
        );

        $leftSidebarContent = $sidebarManager->getSidebarContent('left', $page, $lang_code);
        $rightSidebarContent = $sidebarManager->getSidebarContent('right', $page, $lang_code);

        return [
            'site_data' => $siteData,
            'nav_data' => $navData,
            'footer_data' => $footerData,
            'left_sidebar_content' => $leftSidebarContent,
            'right_sidebar_content' => $rightSidebarContent,
        ];
    }
}