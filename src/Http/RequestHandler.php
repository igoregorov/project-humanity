<?php
declare(strict_types=1);

// src/Http/RequestHandler.php

namespace App\Http;

use App\Infrastructure\ContainerInterface;
use App\Http\Controllers\ControllerInterface;
use Throwable;

class RequestHandler
{
    private ContainerInterface $container;
    private Router $router;

    public function __construct(ContainerInterface $container)
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