<?php
// app/bootstrap.php
declare(strict_types=1);

use function App\Http\sendCleanErrorPage;

require_once __DIR__ . '/../vendor/autoload.php';

/**
 * Корень проекта — каталог, содержащий app/, src/, content/ и composer.json.
 */
if (!defined('PROJECT_ROOT')) {
    define('PROJECT_ROOT', dirname(__DIR__));
}

if (!defined('CONTENT_DIR')) {
    define('CONTENT_DIR', PROJECT_ROOT . '/content');
}

if (!defined('BOOKS_DIR')) {
    define('BOOKS_DIR', CONTENT_DIR . '/books');
}

if (PHP_SAPI !== 'cli') {
    // ==========================================================
    // ЗАЩИТА ОТ REVERSE-PROXY КЛОНОВ (foodinnovation.ru и подобных)
    // ==========================================================
    $allowedHosts = ['projecthumanity.space', 'www.projecthumanity.space'];
    $currentHost = $_SERVER['HTTP_HOST'] ?? '';
    // Отрезаем порт, если он указан (актуально для локальной разработки)
    $hostWithoutPort = preg_replace('/:\d+$/', '', $currentHost);

    if (!in_array($hostWithoutPort, $allowedHosts, true)) {
        http_response_code(403);
        header('Content-Type: text/plain; charset=UTF-8');
        echo 'Forbidden: Invalid Host';
        exit(1);
    }
    // ==========================================================

    ini_set('display_errors', '0'); // Всегда 0 в продакшене
    ini_set('log_errors', '1');

    // Заголовки безопасности
    header('Content-Type: text/html; charset=UTF-8');
    header('X-Content-Type-Options: nosniff');
    header('X-Frame-Options: DENY');
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
    header('Referrer-Policy: strict-origin-when-cross-origin');
}

// --- Инициализация сессии ---
if (PHP_SAPI !== 'cli' && session_status() === PHP_SESSION_NONE) {
    $isHttps = (
        (!empty($_SERVER['HTTPS']) && strtolower((string) $_SERVER['HTTPS']) !== 'off')
        || (int) ($_SERVER['SERVER_PORT'] ?? 0) === 443
        || strtolower((string) ($_SERVER['HTTP_X_FORWARDED_PROTO'] ?? '')) === 'https'
    );

    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => $isHttps,
        'httponly' => true,
        'samesite' => 'Strict',
    ]);

    session_start();
}

set_error_handler(
    function (int $severity, string $message, string $file, int $line): bool {
        error_log("PHP Error caught by set_error_handler: $severity - $message in $file:$line");

        if ($severity === E_DEPRECATED || $severity === E_USER_DEPRECATED) {
            return true;
        }

        if (!(error_reporting() & $severity)) {
            return false;
        }

        throw new ErrorException($message, 0, $severity, $file, $line);
    }
);

set_exception_handler(function (Throwable $e): void {
    error_log(
        'Uncaught Exception: ' . $e->getMessage()
        . ' in ' . $e->getFile() . ':' . $e->getLine()
        . PHP_EOL . $e->getTraceAsString()
    );

    sendCleanErrorPage(
        $e->getMessage(),
        $e->getFile(),
        (string) $e->getLine(),
        $e->getTraceAsString(),
        false
    );
});

$container = require_once PROJECT_ROOT . '/container.php';

if (!isset($container)) {
    throw new RuntimeException('Application configuration error: Container is not set.');
}

return $container;