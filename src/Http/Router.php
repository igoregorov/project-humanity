<?php
// src/Http/Router.php

declare(strict_types=1);

namespace App\Http;

use App\Infrastructure\SimpleContainer;
use App\Http\Controllers\ControllerInterface; // Нужно будет создать
use Throwable;

/**
 * Класс Router отвечает за сопоставление входящих запросов с контроллерами.
 * Он использует контейнер для получения экземпляров контроллеров.
 */
class Router
{
    private array $routes = []; // Структура: ['page_name' => ['controller_class' => '...', 'action' => '...']]
    private SimpleContainer $container;

    public function __construct(SimpleContainer $container)
    {
        $this->container = $container;
    }

    /**
     * Регистрирует новый маршрут.
     *
     * @param string $page Название страницы (например, 'home', 'about')
     * @param string $controllerClass Полное имя класса контроллера
     * @param string $action Метод контроллера для вызова (по умолчанию 'handle')
     * @return void
     */
    public function addRoute(string $page, string $controllerClass, string $action = 'handle'): void
    {
        $this->routes[$page] = [
            'controller_class' => $controllerClass,
            'action' => $action,
        ];
    }

    /**
     * Разрешает маршрут на основе параметров запроса ($_GET).
     * Возвращает экземпляр контроллера и имя метода для вызова.
     *
     * @return array ['controller' => ControllerInterface, 'action' => string]
     * @throws RouterException Если маршрут не найден или контроллер некорректен.
     */
    public function resolve(): array
    {
        $page = $_GET['page'] ?? 'home';
        $lang = $_GET['lang'] ?? 'ru';

        // Проверка разрешенного языка (можно вынести в отдельный сервис, если используется часто)
        $config = $this->container->get('config');
        $allowedLanguages = $config['app']['allowed_languages'] ?? ['ru'];
        if (!in_array($lang, $allowedLanguages, true)) {
            $lang = $allowedLanguages[0] ?? 'ru';
        }

        // Установка языка в контейнере
        $this->container->set('request_lang', $lang);

        if (!isset($this->routes[$page])) {
            throw new RouterException("Маршрут для страницы '$page' не найден.");
        }

        $routeConfig = $this->routes[$page];
        $controllerClass = $routeConfig['controller_class'];
        $action = $routeConfig['action'];

        // Получаем контроллер из контейнера
        $controller = $this->container->get($controllerClass);

        // Проверяем, что контроллер реализует нужный интерфейс
        if (!$controller instanceof ControllerInterface) {
            throw new RouterException("Контроллер '$controllerClass' должен реализовывать интерфейс ControllerInterface.");
        }

        // Проверяем, что метод существует
        if (!method_exists($controller, $action)) {
            throw new RouterException("Метод '$action' не найден в контроллере '$controllerClass'.");
        }

        return [
            'controller' => $controller,
            'action' => $action,
            'lang' => $lang, // Передаём язык, он нужен для вызова метода контроллера
            'page' => $page  // Передаём page, он тоже может понадобиться
        ];
    }
}