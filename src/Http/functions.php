<?php
// src/Http/functions.php
declare(strict_types=1);

namespace App\Http;

use JetBrains\PhpStorm\NoReturn;

#[NoReturn]
function sendCleanErrorPage(string $message = '', string $file = '', string $line = '', string $trace = '', bool $debugMode = false): void
{

    // Очищаем буфер вывода, если что-то уже было отправлено
    if (ob_get_level()) {
        ob_clean();
    }

    http_response_code(500);

    header('Content-Type: text/html; charset=utf-8');

    // --- КРАСИВАЯ СТРАНИЦА ОШИБКИ С ОТЛАДКОЙ ---
    $html = '<!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Ошибка сервера</title>
        <style>
            body { font-family: Arial, sans-serif; background-color: #f5f5f5; display: flex; justify-content: center; align-items: center; height: 100vh; margin: 0; }
            .error-container { background-color: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); max-width: 800px; width: 90%; }
            h1 { color: #d32f2f; margin-top: 0; }
            .error-details { background-color: #f9f9f9; padding: 1rem; border-radius: 4px; margin-top: 1rem; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
        </style>
    </head>
    <body>
        <div class="error-container">
            <h1>Произошла ошибка при генерации страницы</h1>
            <p>Пожалуйста, сообщите об этом администратору.</p>';

    if ($debugMode) {
        $html .= '<div class="error-details">';
        $html .= "<strong>Ошибка:</strong> " . htmlspecialchars($message) . "<br/>";
        if ($file) {
            $html .= "<strong>Файл:</strong> " . htmlspecialchars($file) . "<br/>";
        }
        if ($line) {
            $html .= "<strong>Строка:</strong> " . htmlspecialchars($line) . "<br/>";
        }
        if ($trace) {
            $html .= "<strong>Трассировка стека:</strong><br/>" . htmlspecialchars($trace);
        }
        $html .= '</div>';

        $html .= '</div></body></html>';
    }

    echo $html;
    exit(1);
}