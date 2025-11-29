<?php
// src/Http/Controllers/ControllerInterface.php

declare(strict_types=1);

namespace App\Http\Controllers;

use Throwable;

/**
 * Интерфейс для контроллеров.
 * Все контроллеры должны реализовывать этот интерфейс.
 */
interface ControllerInterface
{
    /**
     * Обрабатывает запрос и возвращает HTML.
     *
     * @param string $page Имя текущей страницы
     * @param string $lang Код языка
     * @return string HTML-контент страницы
     * @throws Throwable
     */
    public function handle(string $page, string $lang): string;
}