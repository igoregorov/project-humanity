<?php
// src/Http/RequestHandler.php
declare(strict_types=1);

namespace App\Http;

use App\Http\Controllers\AboutController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PrinciplesController;
use App\Infrastructure\SimpleContainer;
use Throwable;

class RequestHandler
{
    private SimpleContainer $container;

    public function __construct(SimpleContainer $container)
    {
        $this->container = $container;
    }

    public function handle(): void
    {
        try {
            // --- Получение данных запроса ---
            $page = $_GET['page'] ?? 'home';
            $lang = $_GET['lang'] ?? 'ru';

            // --- Проверка разрешенного языка ---
            $config = $this->container->get('config');
            $allowedLanguages = $config['app']['allowed_languages'] ?? ['ru'];
            if (!in_array($lang, $allowedLanguages, true)) {
                $lang = $allowedLanguages[0] ?? 'ru'; // По умолчанию первый в списке
            }

            // --- Установка языка в контейнере (если не было раньше) ---
            // В идеале, это делается в container.php, но на всякий случай
            if ($this->container->get('request_lang') !== $lang) {
                $this->container->set('request_lang', $lang);
            }

            // --- Определение контроллера ---
            $controllerClass = match ($page) {
                'about' => AboutController::class,
                'principles' => PrinciplesController::class,
                default => HomeController::class,
            };

            // --- Вызов контроллера ---
            $controller = new $controllerClass($this->container);
            $response = $controller->handle($page, $lang);

            // --- Вывод результата ---
            echo $response;

        } catch (Throwable $e) {
            $config = $this->container->get('config');
            $debugMode = $config['app']['debug'] ?? false;
            error_log("Exception in RequestHandler: " . $e->getMessage());
            sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString(), $debugMode);
        }
    }
}