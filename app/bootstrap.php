<?php
// app/bootstrap.php
declare(strict_types=1);

// --- Загрузка зависимостей ---
use function App\Http\sendCleanErrorPage;

require_once __DIR__ . '/../vendor/autoload.php';

// --- Безопасность среды ---
if (PHP_SAPI !== 'cli') {
    ini_set('display_errors', '0'); // Всегда 0 в продакшене
    ini_set('log_errors', '1');

    // Заголовки безопасности
    header("Content-Type: text/html; charset=UTF-8");
    header("X-Content-Type-Options: nosniff");
    header("X-Frame-Options: DENY");
    header("X-XSS-Protection: 1; mode=block");
    header("Content-Security-Policy: default-src 'self'; script-src 'self'; style-src 'self' 'unsafe-inline';");
    header("Referrer-Policy: strict-origin-when-cross-origin");
}

// --- Инициализация сессии ---
if (session_status() === PHP_SESSION_NONE) {
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => '/',
        'domain' => '',
        'secure' => isset($_SERVER['HTTPS']),
        'httponly' => true,
        'samesite' => 'Strict'
    ]);
    session_start();
    session_regenerate_id(true);
}

set_error_handler(/**
 * @throws ErrorException
 */ function ($severity, $message, $file, $line) {
    error_log("PHP Error caught by set_error_handler: $severity - $message in $file:$line");
    if (!(error_reporting() & $severity)) {
        return false;
    }
    throw new ErrorException($message, 0, $severity, $file, $line);
});

set_exception_handler(function (Throwable $e) {

    error_log("Uncaught Exception: " . $e->getMessage() . " in " . $e->getFile() . ":" . $e->getLine() . "\n" . $e->getTraceAsString());

    sendCleanErrorPage($e->getMessage(), $e->getFile(), (string) $e->getLine(), $e->getTraceAsString(), true);
});

$container = require_once __DIR__ . '/../container.php';

// --- Валидация контейнера ---
if (!isset($container)) {
    throw new RuntimeException('Application configuration error: Container is not set.');
}

// --- Возврат контейнера для использования в index.php ---
return $container; // Вот этот return!