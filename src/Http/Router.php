<?php
declare(strict_types=1);

namespace App\Http;

use App\Infrastructure\ContainerInterface;
use App\Http\Controllers\ControllerInterface;

class Router
{
    private array $routes = [];
    private ContainerInterface $container;

    public function __construct(ContainerInterface $container)
    {
        $this->container = $container;
    }

    public function addRoute(string $page, string $controllerClass, string $action = 'handle'): void
    {
        $this->routes[$page] = [
            'controller_class' => $controllerClass,
            'action' => $action,
        ];
    }

    /**
     * Возвращает все зарегистрированные маршруты.
     * Используется для генерации sitemap.
     */
    public function getAllRoutes(): array
    {
        return $this->routes;
    }

    public function resolve(): array
    {
        $page = $_GET['page'] ?? 'home';
        $lang = $_GET['lang'] ?? 'ru';

        $config = $this->container->get('config');
        $allowedLanguages = ['ru'];
        if (is_array($config)) {
            $appConfig = $config['app'] ?? [];
            if (is_array($appConfig) && isset($appConfig['allowed_languages']) && is_array($appConfig['allowed_languages'])) {
                $allowedLanguages = $appConfig['allowed_languages'];
            }
        }

        if ($allowedLanguages === []) {
            $allowedLanguages = ['ru'];
        }

        if (!in_array($lang, $allowedLanguages, true)) {
            $lang = $allowedLanguages[0] ?? 'ru';
        }

        $this->container->set('request_lang', $lang);

        if (!isset($this->routes[$page])) {
            throw new RouterException("Маршрут для страницы '$page' не найден.");
        }

        $routeConfig = $this->routes[$page];
        $controllerClass = $routeConfig['controller_class'];
        $action = $routeConfig['action'];

        $controller = $this->container->get($controllerClass);

        if (!$controller instanceof ControllerInterface) {
            throw new RouterException("Контроллер '$controllerClass' должен реализовывать интерфейс ControllerInterface.");
        }

        if (!method_exists($controller, $action)) {
            throw new RouterException("Метод '$action' не найден в контроллере '$controllerClass'.");
        }

        return [
            'controller' => $controller,
            'action' => $action,
            'lang' => $lang,
            'page' => $page
        ];
    }
}