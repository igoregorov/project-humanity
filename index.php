<?php
// index.php
declare(strict_types=1);

// Подключаем ядро приложения и получаем контейнер
use App\Http\RequestHandler;
use function App\Http\sendCleanErrorPage;

$container = require_once __DIR__ . '/app/bootstrap.php';

try {
    $requestHandler = new RequestHandler($container);
    $requestHandler->handle();

} catch (\Throwable $e) {
    $config = $container->get('config');
    $debugMode = $config['app']['debug'] ?? true;
    sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString(), $debugMode);
}