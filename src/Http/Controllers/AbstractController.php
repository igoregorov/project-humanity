<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infrastructure\ContainerInterface;
use App\Http\ViewRenderer;
use App\View\TemplateDataInterface;
use Throwable;

abstract class AbstractController implements ControllerInterface
{
    protected ContainerInterface $container;
    protected ViewRenderer $viewRenderer;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
        $this->viewRenderer = new ViewRenderer(__DIR__ . '/../../../includes/', 'layout.php');
    }

    protected function renderPage(
        string $templateName,
        TemplateDataInterface $pageData,
        string $page,
        string $lang
    ): string {
        $pageDataService = $this->container->get('page_data_service');
        $preparedData = $pageDataService->prepareLayoutData($page, $lang);

        $siteData = $preparedData['site_data'];
        $navData = $preparedData['nav_data'];
        $footerData = $preparedData['footer_data'];
        $leftSidebarContent = $preparedData['left_sidebar_content'];
        $rightSidebarContent = $preparedData['right_sidebar_content'];

        // Формируем canonical и hreflang
        $canonicalUrl = $this->buildCanonicalUrl($page, $lang);
        $hreflangUrls = $this->buildHreflangUrls($page);

        $layoutData = [
            'lang_code' => $siteData['lang_code'],
            'site_title' => $siteData['site_title'],
            'page_title' => $siteData['page_title'],
            'description' => $siteData['description'],
            'version' => $siteData['version'],
            'page' => $page,
            'canonical_url' => $canonicalUrl,
            'hreflang_urls' => $hreflangUrls,
            'nav_html' => $this->viewRenderer->renderWithoutLayout('nav.php', $navData),
            'left_sidebar_html' => $this->renderSidebar($leftSidebarContent, 'left'),
            'right_sidebar_html' => $this->renderSidebar($rightSidebarContent, 'right'),
            'footer_html' => $this->viewRenderer->renderWithoutLayout('footer.php', $footerData),
            'has_left_sidebar' => !empty($leftSidebarContent),
            'has_right_sidebar' => !empty($rightSidebarContent),
        ];

        return $this->viewRenderer->render($templateName, $pageData, $layoutData);
    }

    /**
     * Формирует canonical URL для страницы.
     * Контроллеры могут переопределять этот метод для страниц с дополнительными параметрами.
     */
    protected function buildCanonicalUrl(string $page, string $lang): string
    {
        $baseUrl = 'https://projecthumanity.space/';

        if ($page === 'home') {
            return $baseUrl . '?lang=' . $lang;
        }

        return $baseUrl . '?page=' . $page . '&lang=' . $lang;
    }

    /**
     * Формирует hreflang URL для всех языков.
     * Контроллеры могут переопределять этот метод для страниц с дополнительными параметрами.
     */
    protected function buildHreflangUrls(string $page): array
    {
        $config = $this->container->get('config');
        $allowedLanguages = $config['app']['allowed_languages'] ?? ['ru', 'en'];

        $urls = [];
        foreach ($allowedLanguages as $langCode) {
            $urls[$langCode] = $this->buildCanonicalUrl($page, $langCode);
        }

        // x-default на основной язык
        $urls['x-default'] = $this->buildCanonicalUrl($page, 'ru');

        return $urls;
    }

    private function renderSidebar(?array $content, string $position): string
    {
        return $content
            ? $this->viewRenderer->renderWithoutLayout('sidebar.php', new \App\View\SidebarData($content, $position))
            : '';
    }
}