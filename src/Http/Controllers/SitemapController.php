<?php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Infrastructure\ContainerInterface;

class SitemapController implements ControllerInterface
{
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    /**
     * Интерфейс ControllerInterface требует эти параметры,
     * но для sitemap они не используются.
     */
    public function handle(string $page, string $lang): string
    {
        header('Content-Type: application/xml; charset=UTF-8');

        $router = $this->container->get('router');
        $config = $this->container->get('config');
        $allowedLanguages = $config['app']['allowed_languages'] ?? ['ru', 'en'];

        $routes = $router->getAllRoutes();

        $xml = '<?xml version="1.0" encoding="UTF-8"?>' . "\n";
        $xml .= '<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"' . "\n";
        $xml .= '        xmlns:xhtml="http://www.w3.org/1999/xhtml">' . "\n";

        foreach ($routes as $pageName => $routeConfig) {
            // Пропускаем сам sitemap — он не должен быть в sitemap
            if ($pageName === 'sitemap') {
                continue;
            }

            // Получаем список URL для данной страницы
            $urls = $this->getUrlsForPage($pageName, $allowedLanguages);

            foreach ($urls as $urlData) {
                $xml .= "  <url>\n";
                $xml .= "    <loc>" . $this->escapeXml($urlData['url']) . "</loc>\n";
                $xml .= "    <lastmod>" . date('Y-m-d') . "</lastmod>\n";
                $xml .= "    <changefreq>" . $urlData['changefreq'] . "</changefreq>\n";
                $xml .= "    <priority>" . $urlData['priority'] . "</priority>\n";

                // Hreflang для всех языковых версий
                foreach ($allowedLanguages as $hreflang) {
                    $href = $this->buildUrl($pageName, $hreflang, $urlData['params'] ?? []);
                    $xml .= '    <xhtml:link rel="alternate" hreflang="'
                        . $this->escapeXml($hreflang)
                        . '" href="'
                        . $this->escapeXml($href)
                        . "\" />\n";
                }
                // x-default на русский
                $defaultHref = $this->buildUrl($pageName, 'ru', $urlData['params'] ?? []);
                $xml .= '    <xhtml:link rel="alternate" hreflang="x-default" href="'
                    . $this->escapeXml($defaultHref)
                    . "\" />\n";

                $xml .= "  </url>\n";
            }
        }

        $xml .= "</urlset>\n";

        return $xml;
    }

    /**
     * Возвращает список URL для страницы.
     * Для страниц с доп. параметрами (например, license) возвращает несколько URL.
     */
    private function getUrlsForPage(string $pageName, array $allowedLanguages): array
    {
        $priority = $this->getPriority($pageName);
        $changefreq = $this->getChangefreq($pageName);

        return match ($pageName) {
            'license' => $this->getLicenseUrls($priority, $changefreq),
            default => [
                [
                    'url' => $this->buildUrl($pageName, $allowedLanguages[0] ?? 'ru'),
                    'priority' => $priority,
                    'changefreq' => $changefreq,
                ],
            ],
        };
    }

    /**
     * Для страницы license генерируем URL для каждого документа.
     */
    private function getLicenseUrls(string $priority, string $changefreq): array
    {
        $docs = ['overview', 'book', 'site', 'code', 'dmca'];
        $urls = [];

        foreach ($docs as $doc) {
            $urls[] = [
                'url' => $this->buildUrl('license', 'ru', ['doc' => $doc]),
                'priority' => $priority,
                'changefreq' => $changefreq,
                'params' => ['doc' => $doc],
            ];
        }

        return $urls;
    }

    /**
     * Формирует URL для страницы.
     * Логика строго согласована с AbstractController::buildCanonicalUrl().
     */
    private function buildUrl(string $page, string $lang, array $extraParams = []): string
    {
        $baseUrl = 'https://projecthumanity.space/';

        // Порядок параметров строго как в AbstractController: сначала page, потом lang
        if ($page === 'home') {
            $params = ['lang' => $lang];
        } else {
            $params = ['page' => $page, 'lang' => $lang];
        }

        $params = array_merge($params, $extraParams);

        // Разделитель '&' (не '&amp;'). Метод escapeXml сам превратит его в '&amp;' для XML.
        return $baseUrl . '?' . http_build_query($params, '', '&', PHP_QUERY_RFC3986);
    }

    private function getPriority(string $page): string
    {
        return match ($page) {
            'home' => '1.0',
            'about', 'principles', 'living_book', 'books' => '0.8',
            'license' => '0.5',
            'auth' => '0.3',
            default => '0.5',
        };
    }

    private function getChangefreq(string $page): string
    {
        return match ($page) {
            'home', 'living_book' => 'daily',
            'about', 'principles', 'books' => 'weekly',
            'license', 'auth' => 'monthly',
            default => 'weekly',
        };
    }

    private function escapeXml(string $value): string
    {
        return htmlspecialchars($value, ENT_XML1 | ENT_QUOTES, 'UTF-8');
    }
}