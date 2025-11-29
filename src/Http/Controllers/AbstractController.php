<?php
// src/Http/Controllers/AbstractController.php
declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\ViewRenderer;
use App\Infrastructure\SimpleContainer;
use App\View\NavData;
use App\View\SidebarLeftData;
use App\View\SidebarRightData;
use App\View\FooterData;
use App\View\TemplateDataInterface;
use Throwable;

abstract class AbstractController
{
    protected SimpleContainer $container;
    protected ViewRenderer $viewRenderer; // Теперь с layout

    public function __construct(SimpleContainer $container)
    {
        $this->container = $container;
        // Создаем ViewRenderer с указанием layout.php
        $this->viewRenderer = new ViewRenderer(__DIR__ . '/../../../includes/', 'layout.php');
    }

    /**
     * Общий метод для рендеринга страницы с layout.
     * @param string $templateName Имя шаблона контентной части (например, 'main_content.php')
     * @param TemplateDataInterface $pageData Данные для контентного шаблона
     * @param string $page Текущая страница (для навигации)
     * @param string $lang Язык
     * @return string Полный HTML
     * @throws Throwable
     */
    protected function renderPageWithLayout(
        string $templateName,
        TemplateDataInterface $pageData,
        string $page,
        string $lang
    ): string {
        // Получаем общие данные сайта
        $siteData = $this->container->get('site_data');
        $translator = $siteData['translator'];
        $lang_code = $siteData['lang_code'];
        $upcoming_events = $siteData['events_data'];
        $news_items = $siteData['news_data'];
        $site_title = $siteData['site_title'];
        $page_title = $siteData['page_title'];
        $description = $siteData['description'];
        $version = $siteData['version'];

        // Подготовка данных для компонентов
        $navData = new NavData(current_lang: $lang_code, current_page: $page, translator: $translator);
        $sidebarLeftData = new SidebarLeftData(translator: $translator, upcoming_events: $upcoming_events, lang_code: $lang_code);
        $sidebarRightData = new SidebarRightData(translator: $translator, news_items: $news_items, lang_code: $lang_code);
        $footerData = new FooterData(site_title: $site_title, translator: $translator, lang_code: $lang_code);

        // Рендерим компоненты *вручную* через $this->viewRenderer
        // Обрати внимание: для компонентов мы используем layout = null или пустой шаблон.
        // Нам нужен только их HTML, без обертки layout.php
        $navHtml = $this->viewRenderer->renderWithoutLayout('nav.php', $navData);
        $sidebarLeftHtml = $this->viewRenderer->renderWithoutLayout('sidebar_left.php', $sidebarLeftData);
        $sidebarRightHtml = $this->viewRenderer->renderWithoutLayout('sidebar_right.php', $sidebarRightData);
        $footerHtml = $this->viewRenderer->renderWithoutLayout('footer.php', $footerData);

        // Подготовка данных для layout
        $layoutData = [
            'lang_code' => $lang_code,
            'site_title' => $site_title,
            'page_title' => $page_title,
            'description' => $description,
            'version' => $version,
            'page' => $page, // Для ссылок переключения языка
            // Передаём уже отрендеренные строки
            'nav_html' => $navHtml,
            'sidebar_left_html' => $sidebarLeftHtml,
            'sidebar_right_html' => $sidebarRightHtml,
            'footer_html' => $footerHtml,
        ];

        // Вызываем ViewRenderer, передав ему шаблон контента, его данные и данные для layout
        // Главное: layout.php НЕ должен сам рендерить компоненты через ViewRenderer!
        return $this->viewRenderer->render($templateName, $pageData, $layoutData);
    }
}