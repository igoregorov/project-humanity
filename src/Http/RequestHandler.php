<?php
// src/Http/RequestHandler.php
declare(strict_types=1);

namespace App\Http;

use App\Http\Controllers\ControllerInterface;
use App\Infrastructure\SimpleContainer;
use Throwable;

class RequestHandler
{
    private SimpleContainer $container;
    private Router $router;

    public function __construct(SimpleContainer $container)
    {
        $this->container = $container;
        $this->router = $container->get('router');
    }

    public function handle(): void
    {
        try {
            // --- РАЗРЕШАЕМ маршрут через роутер ---
            $resolved = $this->router->resolve();

            /** @var ControllerInterface $controller */
            $controller = $resolved['controller'];
            $action = $resolved['action'];
            $lang = $resolved['lang'];
            $page = $resolved['page'];

            // --- Вызов контроллера ---
            $response = $controller->$action($page, $lang);

            // --- Вывод результата ---
            echo $response;
        } catch (RouterException $e) {
            $config = $this->container->get('config');
            $debugMode = $config['app']['debug'] ?? false;
            error_log("Router Exception in RequestHandler: " . $e->getMessage());
            sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString(), $debugMode);
        } catch (Throwable $e) {
            $config = $this->container->get('config');
            $debugMode = $config['app']['debug'] ?? false;
            error_log("General Exception in RequestHandler: " . $e->getMessage());
            sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString(), $debugMode);
        }
    }
}