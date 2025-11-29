<?php
// src/Http/Controllers/AboutController.php
declare(strict_types=1);

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
        // Получаем специфичный сервис для 'about'
        $about_service = $this->container->get('about_service');

        // Подготовка данных для шаблона 'page_about.php'
        $aboutData = new PageAboutData(translator: $about_service, lang_code: $lang);

        // Используем общий метод для рендеринга с layout
        return $this->renderPageWithLayout('page_about.php', $aboutData, $page, $lang);
    }
}